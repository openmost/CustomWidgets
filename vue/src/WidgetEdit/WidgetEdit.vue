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
              <div class="customWidgetPreview__header">
                {{ widget.title.trim() || translate('CustomWidgets_UntitledWidget') }}
              </div>
              <div
                class="customWidgetPreview__content widgetBody custom-widget-body"
                v-html="widget.content"
              />
            </div>
            <p class="customWidgetEdit__help">{{ translate('CustomWidgets_PreviewScriptsNotice') }}</p>
          </div>
        </div>

        <div class="customWidgetEdit__sites">
          <h3 class="customWidgetEdit__heading">{{ translate('CustomWidgets_DisplayOn') }}</h3>
          <p>
            <label>
              <input
                type="radio"
                name="customWidgetScope"
                :checked="!specificSites"
                @change="specificSites = false"
              />
              <span>{{ translate('CustomWidgets_AllWebsites') }}</span>
            </label>
          </p>
          <p>
            <label>
              <input
                type="radio"
                name="customWidgetScope"
                :checked="specificSites"
                @change="specificSites = true"
              />
              <span>{{ translate('CustomWidgets_SpecificWebsites') }}</span>
            </label>
          </p>

          <div
            v-if="specificSites"
            class="customWidgetEdit__siteSelection"
          >
            <input
              v-model="siteSearch"
              type="text"
              class="customWidgetEdit__siteSearch"
              :placeholder="translate('CustomWidgets_SearchWebsites')"
            />
            <div class="customWidgetEdit__siteList">
              <p
                v-for="site in filteredSites"
                :key="site.idsite"
              >
                <label>
                  <input
                    type="checkbox"
                    :checked="widget.idSites.includes(site.idsite)"
                    @change="toggleSite(site.idsite)"
                  />
                  <span>{{ site.name }} <small>#{{ site.idsite }}</small></span>
                </label>
              </p>
              <p
                v-if="!filteredSites.length"
                class="customWidgetEdit__help"
              >{{ translate('CustomWidgets_NoWebsiteFound') }}</p>
            </div>
            <p class="customWidgetEdit__help">
              {{ widget.idSites.length
                ? translate('CustomWidgets_SelectedWebsites', String(widget.idSites.length))
                : translate('CustomWidgets_NoWebsiteSelected') }}
            </p>
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
  translate,
} from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';
import HtmlCodeEditor from '../HtmlCodeEditor/HtmlCodeEditor.vue';
import CustomWidgetsStore from '../CustomWidgets.store';
import type { CustomWidget } from '../types';

export interface WidgetEditState {
  widget: CustomWidget;
  specificSites: boolean;
  siteSearch: string;
  isLoading: boolean;
  notFound: boolean;
}

export default defineComponent({
  name: 'WidgetEdit',
  components: {
    ContentBlock,
    Field,
    HtmlCodeEditor,
    MatomoLoader,
    SaveButton,
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
      specificSites: false,
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
    CustomWidgetsStore.fetchWidget(this.idWidget).then((widget) => {
      this.widget = widget;
      this.specificSites = widget.idSites.length > 0;
    }).catch(() => {
      this.notFound = true;
    }).finally(() => {
      this.isLoading = false;
    });
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
    filteredSites() {
      const search = this.siteSearch.trim().toLowerCase();
      const sites = CustomWidgetsStore.sites.value;
      if (!search) {
        return sites;
      }
      return sites.filter((site) => site.name.toLowerCase().includes(search)
        || String(site.idsite) === search);
    },
  },
  methods: {
    toggleSite(idSite: number) {
      const position = this.widget.idSites.indexOf(idSite);
      if (position === -1) {
        this.widget.idSites.push(idSite);
      } else {
        this.widget.idSites.splice(position, 1);
      }
    },
    save() {
      const widget: CustomWidget = {
        ...this.widget,
        idSites: this.specificSites ? [...this.widget.idSites] : [],
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
