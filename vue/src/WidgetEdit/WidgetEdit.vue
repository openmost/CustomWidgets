<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="customWidgetEdit">
    <ContentBlock :content-title="contentTitle">
      <p v-if="isLoading">
        <span class="loadingPiwik">
          <MatomoLoader />
          {{ translate('General_LoadingData') }}
        </span>
      </p>

      <div v-else-if="notFound">
        <p>{{ translate('CustomWidgets_WidgetNotFound') }}</p>
        <a
          class="btn"
          href="#?"
        >{{ translate('General_Cancel') }}</a>
      </div>

      <div v-else>
        <Field
          uicontrol="text"
          name="customWidgetTitle"
          v-model="widget.title"
          :maxlength="255"
          :required="true"
          :title="translate('CustomWidgets_WidgetTitle')"
          :inline-help="translate('CustomWidgets_WidgetTitleHelp')"
        />

        <div class="customWidgetEdit__workspace">
          <div class="customWidgetEdit__pane">
            <h3 class="customWidgetEdit__heading">{{ translate('CustomWidgets_WidgetContent') }}</h3>
            <HtmlCodeEditor v-model="widget.content" />
            <p class="customWidgetEdit__help">{{ translate('CustomWidgets_WidgetContentHelp') }}</p>
          </div>

          <div class="customWidgetEdit__pane">
            <h3 class="customWidgetEdit__heading">{{ translate('CustomWidgets_Preview') }}</h3>
            <div class="customWidgetPreview">
              <div class="widget default">
                <div class="widgetTop">
                  <ReportHeader
                    context="preview"
                    :report-title="widget.title.trim() || translate('CustomWidgets_UntitledWidget')"
                  />
                </div>
                <div class="widgetContent">
                  <div
                    ref="previewBody"
                    class="widgetBody custom-widget-body"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="customWidgetEdit__sites">
          <h3 class="customWidgetEdit__heading">{{ translate('CustomWidgets_ApplyTo') }}</h3>
          <div class="customWidgetEdit__siteSelector">
            <label
              for="customWidgetSite"
              class="siteSelectorLabel"
            >{{ translate('General_Website') }}</label>
            <div class="sites_autocomplete">
              <SiteSelector
                id="customWidgetSite"
                :model-value="site"
                @update:model-value="onSiteSelected($event)"
                :show-all-sites-item="true"
                :all-sites-text="translate('CustomWidgets_AllWebsites')"
                all-sites-location="top"
                :switch-site-on-select="false"
                :show-selected-site="true"
              />
            </div>
          </div>

          <div
            v-if="!isAllWebsites"
            class="customWidgetEdit__siteSelection"
          >
            <label for="customWidgetSiteSearch">
              {{ translate('CustomWidgets_SelectWebsitesMatchingSearch') }}
            </label>
            <div class="customWidgetEdit__siteSearch">
              <input
                id="customWidgetSiteSearch"
                v-model="siteSearch"
                type="text"
                class="control_text"
                :placeholder="translate('General_Search')"
                @keydown.enter.prevent="addSitesMatching(siteSearch)"
              />
              <input
                type="button"
                class="btn"
                :disabled="!siteSearch.trim()"
                :value="translate('CustomWidgets_FindWebsites')"
                @click="addSitesMatching(siteSearch)"
              />
            </div>

            <table class="entityTable">
              <thead>
                <tr>
                  <th class="siteId">{{ translate('General_Id') }}</th>
                  <th class="siteName">{{ translate('General_Name') }}</th>
                  <th class="siteAction">{{ translate('General_Remove') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="selectedSite in selectedSites"
                  :key="selectedSite.idsite"
                >
                  <td>{{ selectedSite.idsite }}</td>
                  <td>{{ selectedSite.name }}</td>
                  <td class="siteAction entityTable_ActionCell">
                    <button
                      v-if="selectedSites.length > 1"
                      type="button"
                      class="table-action icon-minus"
                      :title="translate('General_Remove')"
                      @click="removeSite(selectedSite.idsite)"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="customWidgetEdit__buttons">
          <SaveButton
            :value="idWidget ? translate('General_Update') : translate('General_Create')"
            :saving="isUpdating"
            @confirm="save()"
          />
          <a
            class="btn-flat"
            href="#?"
          >{{ translate('General_Cancel') }}</a>
        </div>
      </div>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  MatomoLoader,
  MatomoUrl,
  NotificationsStore,
  ReportHeader,
  SiteSelector,
  translate,
} from 'CoreHome';
import type { SiteRef } from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';
import HtmlCodeEditor from '../HtmlCodeEditor/HtmlCodeEditor.vue';
import formatContent from './formatContent';
import CustomWidgetsStore from '../CustomWidgets.store';
import type { CustomWidget, SiteOption } from '../types';

const ALL_WEBSITES = 'all';

// scripts of the content run in the preview: wait for the end of the typing to not run half written code
const PREVIEW_DELAY = 600;

let previewTimeout: ReturnType<typeof setTimeout> | undefined;

export interface WidgetEditState {
  widget: CustomWidget;
  site: SiteRef;
  siteSearch: string;
  isLoading: boolean;
  notFound: boolean;
}

function allWebsites(): SiteRef {
  return { id: ALL_WEBSITES, name: translate('CustomWidgets_AllWebsites') };
}

export default defineComponent({
  name: 'WidgetEdit',
  components: {
    ContentBlock,
    Field,
    HtmlCodeEditor,
    MatomoLoader,
    ReportHeader,
    SaveButton,
    SiteSelector,
  },
  props: {
    // 0 to create a widget
    idWidget: {
      type: Number,
      required: true,
    },
  },
  data(): WidgetEditState {
    return {
      widget: {
        id: 0,
        title: '',
        content: '',
        idSites: [],
      },
      site: allWebsites(),
      siteSearch: '',
      isLoading: false,
      notFound: false,
    };
  },
  created() {
    if (!this.idWidget) {
      return;
    }

    this.isLoading = true;
    Promise.all([
      CustomWidgetsStore.fetchWidget(this.idWidget),
      CustomWidgetsStore.fetchSites(),
    ]).then(([widget]) => {
      this.widget = widget;
      if (widget.idSites.length) {
        this.site = this.toSiteRef(widget.idSites[0]);
      }
    }).catch(() => {
      this.notFound = true;
    }).finally(() => {
      this.isLoading = false;
    });
  },
  watch: {
    'widget.content': function onContentChange() {
      clearTimeout(previewTimeout);
      previewTimeout = setTimeout(() => this.renderPreview(), PREVIEW_DELAY);
    },
    isLoading(isLoading: boolean) {
      if (!isLoading) {
        this.$nextTick(() => this.renderPreview());
      }
    },
  },
  mounted() {
    this.renderPreview();
  },
  beforeUnmount() {
    clearTimeout(previewTimeout);
  },
  computed: {
    contentTitle(): string {
      if (!this.idWidget) {
        return translate('CustomWidgets_CreateWidget');
      }
      return translate('CustomWidgets_EditWidget', `"${this.widget.title}"`);
    },
    isUpdating(): boolean {
      return CustomWidgetsStore.isUpdating.value;
    },
    isAllWebsites(): boolean {
      return `${this.site.id}` === ALL_WEBSITES;
    },
    selectedSites(): SiteOption[] {
      const names = CustomWidgetsStore.siteNames.value;
      return this.widget.idSites.map((idsite) => ({
        idsite,
        name: names[idsite] ?? `#${idsite}`,
      }));
    },
  },
  methods: {
    renderPreview() {
      const previewBody = this.$refs.previewBody as HTMLElement | undefined;
      if (previewBody) {
        // jQuery executes the scripts, like the dashboards rendering the widget
        window.$(previewBody).html(formatContent(this.widget.content));
      }
    },
    toSiteRef(idSite: number): SiteRef {
      return {
        id: idSite,
        name: CustomWidgetsStore.siteNames.value[idSite] ?? `#${idSite}`,
      };
    },
    onSiteSelected(site: SiteRef) {
      this.site = site;
      if (this.isAllWebsites) {
        this.widget.idSites = [];
        return;
      }
      this.addSite(Number(site.id));
    },
    addSite(idSite: number) {
      if (!this.widget.idSites.includes(idSite)) {
        this.widget.idSites.push(idSite);
      }
    },
    removeSite(idSite: number) {
      if (this.widget.idSites.length <= 1) {
        return;
      }

      this.widget.idSites = this.widget.idSites.filter((id) => id !== idSite);
      if (Number(this.site.id) === idSite) {
        this.site = this.toSiteRef(this.widget.idSites[0]);
      }
    },
    addSitesMatching(searchTerm: string) {
      const search = searchTerm.trim().toLowerCase();
      if (!search) {
        return;
      }

      const matching = CustomWidgetsStore.sites.value.filter(
        (site) => site.name.toLowerCase().includes(search) || String(site.idsite) === search,
      );
      const added = matching.filter((site) => !this.widget.idSites.includes(site.idsite));
      added.forEach((site) => this.addSite(site.idsite));

      NotificationsStore.show({
        message: matching.length
          ? translate('CustomWidgets_WebsitesAdded', String(added.length), `"${searchTerm.trim()}"`)
          : translate('CustomWidgets_NoWebsiteMatching', `"${searchTerm.trim()}"`),
        context: matching.length ? 'success' : 'warning',
        id: 'customWidgetsSiteSearch',
        type: 'transient',
      });
      if (matching.length) {
        this.siteSearch = '';
      }
    },
    save() {
      const widget: CustomWidget = {
        ...this.widget,
        idSites: this.isAllWebsites ? [] : [...this.widget.idSites],
      };

      CustomWidgetsStore.saveWidget(widget).then(() => {
        NotificationsStore.show({
          message: translate(this.idWidget ? 'CustomWidgets_WidgetUpdated' : 'CustomWidgets_WidgetCreated'),
          context: 'success',
          id: 'customWidgetsNotification',
          type: 'transient',
        });
        MatomoUrl.updateHashToUrl('/');
      });
    },
  },
});
</script>
