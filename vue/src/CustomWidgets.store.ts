/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, reactive, readonly } from 'vue';
import { AjaxHelper, Matomo } from 'CoreHome';
import type { CustomWidget, SiteOption } from './types';

interface CustomWidgetsState {
  widgets: CustomWidget[];
  allowedDomains: string[];
  sites: SiteOption[];
  isLoading: boolean;
  isUpdating: boolean;
}

interface RawWidget {
  id: number | string;
  title: string;
  content: string;
  idSites: Array<number | string>;
}

function toWidget(widget: RawWidget): CustomWidget {
  return {
    id: Number(widget.id),
    title: String(widget.title ?? ''),
    content: String(widget.content ?? ''),
    idSites: (widget.idSites || []).map((idSite) => Number(idSite)),
  };
}

class CustomWidgetsStore {
  private privateState = reactive<CustomWidgetsState>({
    widgets: [],
    allowedDomains: [],
    sites: [],
    isLoading: false,
    isUpdating: false,
  });

  readonly state = computed(() => readonly(this.privateState));

  readonly widgets = computed(() => this.state.value.widgets);

  readonly allowedDomains = computed(() => this.state.value.allowedDomains);

  readonly sites = computed(() => this.state.value.sites);

  readonly siteNames = computed(() => {
    const names: Record<number, string> = {};
    this.state.value.sites.forEach((site) => {
      names[site.idsite] = site.name;
    });
    return names;
  });

  readonly isLoading = computed(() => this.state.value.isLoading);

  readonly isUpdating = computed(() => this.state.value.isUpdating);

  private sitesPromise: Promise<void> | null = null;

  fetch(): Promise<void> {
    this.privateState.isLoading = true;

    return Promise.all([
      this.fetchWidgets(),
      this.fetchAllowedDomains(),
      this.fetchSites(),
    ]).then(() => undefined).finally(() => {
      this.privateState.isLoading = false;
    });
  }

  fetchWidgets(): Promise<void> {
    return AjaxHelper.fetch<RawWidget[]>({
      method: 'CustomWidgets.getWidgets',
      filter_limit: '-1',
    }).then((widgets) => {
      this.privateState.widgets = (widgets || []).map(toWidget);
    });
  }

  fetchWidget(idWidget: number): Promise<CustomWidget> {
    return AjaxHelper.fetch<RawWidget>({
      method: 'CustomWidgets.getWidget',
      idWidget,
    }).then(toWidget);
  }

  fetchAllowedDomains(): Promise<void> {
    return AjaxHelper.fetch<string[]>({
      method: 'CustomWidgets.getAllowedDomains',
      filter_limit: '-1',
    }).then((domains) => {
      this.privateState.allowedDomains = domains || [];
    });
  }

  fetchSites(): Promise<void> {
    if (!this.sitesPromise) {
      this.sitesPromise = AjaxHelper.fetch<Array<{ idsite: number | string, name: string }>>({
        method: 'SitesManager.getSitesWithAdminAccess',
        filter_limit: '-1',
      }).then((sites) => {
        this.privateState.sites = (sites || []).map((site) => ({
          idsite: Number(site.idsite),
          name: Matomo.helper.htmlDecode(site.name),
        }));
      });
    }

    return this.sitesPromise;
  }

  saveWidget(widget: CustomWidget): Promise<number> {
    const postParams = {
      title: widget.title,
      content: widget.content,
      idSites: widget.idSites,
    };

    const request = widget.id
      ? AjaxHelper.post(
        { method: 'CustomWidgets.updateWidget', idWidget: widget.id },
        postParams,
      ).then(() => widget.id)
      : AjaxHelper.post<{ value: number | string }>(
        { method: 'CustomWidgets.addWidget' },
        postParams,
      ).then((response) => Number(response.value));

    return this.update(request.then((idWidget) => this.fetchWidgets().then(() => idWidget)));
  }

  deleteWidget(idWidget: number): Promise<void> {
    return this.update(AjaxHelper.post(
      { method: 'CustomWidgets.deleteWidget' },
      { idWidget },
    ).then(() => this.fetchWidgets()));
  }

  saveAllowedDomains(domains: string[]): Promise<void> {
    return this.update(AjaxHelper.post<string[]>(
      { method: 'CustomWidgets.setAllowedDomains' },
      { domains },
    ).then((saved) => {
      this.privateState.allowedDomains = saved || [];
    }));
  }

  private update<T>(request: Promise<T>): Promise<T> {
    this.privateState.isUpdating = true;

    return request.finally(() => {
      this.privateState.isUpdating = false;
    });
  }
}

export default new CustomWidgetsStore();
