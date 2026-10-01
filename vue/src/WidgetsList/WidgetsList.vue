<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="customWidgetsList">
    <div v-content-intro>
      <h2>
        <EnrichedHeadline>{{ translate('CustomWidgets_CustomWidgets') }}</EnrichedHeadline>
      </h2>
      <p>{{ translate('CustomWidgets_ManageIntro') }}</p>
    </div>

    <ContentBlock :content-title="translate('CustomWidgets_Widgets')">
      <table v-content-table>
        <thead>
          <tr>
            <th class="index">{{ translate('General_Id') }}</th>
            <th class="title">{{ translate('CustomWidgets_WidgetTitle') }}</th>
            <th class="sites">{{ translate('CustomWidgets_DisplayOn') }}</th>
            <th class="action">{{ translate('General_Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="4">
              <span class="loadingPiwik">
                <MatomoLoader />
                {{ translate('General_LoadingData') }}
              </span>
            </td>
          </tr>
          <tr v-else-if="!widgets.length">
            <td colspan="4">{{ translate('CustomWidgets_NoWidgets') }}</td>
          </tr>
          <template v-else>
            <tr
              v-for="widget in widgets"
              :key="widget.id"
              :class="`customWidget customWidget-${widget.id}`"
            >
              <td class="index">{{ widget.id }}</td>
              <td class="title">
                <a :href="`#?idWidget=${widget.id}`">{{ widget.title }}</a>
              </td>
              <td class="sites">
                <span
                  v-if="!widget.idSites.length"
                  class="customWidgetsList__allSites"
                >{{ translate('CustomWidgets_AllWebsites') }}</span>
                <span
                  v-else
                  :title="siteList(widget)"
                >{{ sitesSummary(widget) }}</span>
              </td>
              <td class="action entityTable_ActionCell">
                <a
                  class="table-action icon-edit"
                  :href="`#?idWidget=${widget.id}`"
                  :title="translate('General_Edit')"
                />
                <button
                  type="button"
                  class="table-action icon-delete"
                  :disabled="isUpdating"
                  :title="translate('General_Delete')"
                  @click="deleteWidget(widget)"
                />
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div class="tableActionBar">
        <a
          class="btn customWidgetsList__create"
          href="#?idWidget=0"
        >
          <span class="icon-add" />
          {{ translate('CustomWidgets_CreateWidget') }}
        </a>
      </div>
    </ContentBlock>

    <div
      class="ui-confirm"
      ref="confirm"
    >
      <h2>{{ translate('CustomWidgets_DeleteWidgetConfirm', `"${widgetToDelete?.title || ''}"`) }}</h2>
      <input
        role="yes"
        type="button"
        :value="translate('General_Yes')"
      />
      <input
        role="no"
        type="button"
        :value="translate('General_No')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { DeepReadonly, defineComponent } from 'vue';
import {
  ContentBlock,
  ContentIntro,
  ContentTable,
  EnrichedHeadline,
  Matomo,
  MatomoLoader,
  NotificationsStore,
  translate,
} from 'CoreHome';
import CustomWidgetsStore from '../CustomWidgets.store';
import type { CustomWidget } from '../types';

type ListedWidget = DeepReadonly<CustomWidget>;

export interface WidgetsListState {
  widgetToDelete: ListedWidget | null;
}

const MAX_SITES_IN_SUMMARY = 2;

export default defineComponent({
  name: 'WidgetsList',
  components: {
    ContentBlock,
    EnrichedHeadline,
    MatomoLoader,
  },
  directives: {
    ContentIntro,
    ContentTable,
  },
  data(): WidgetsListState {
    return {
      widgetToDelete: null,
    };
  },
  computed: {
    widgets() {
      return CustomWidgetsStore.widgets.value;
    },
    isLoading(): boolean {
      return CustomWidgetsStore.isLoading.value;
    },
    isUpdating(): boolean {
      return CustomWidgetsStore.isUpdating.value;
    },
  },
  methods: {
    siteNames(widget: ListedWidget): string[] {
      const names = CustomWidgetsStore.siteNames.value;
      return widget.idSites.map((idSite) => names[idSite] || `#${idSite}`);
    },
    siteList(widget: ListedWidget): string {
      return this.siteNames(widget).join(', ');
    },
    sitesSummary(widget: ListedWidget): string {
      const names = this.siteNames(widget);
      if (names.length <= MAX_SITES_IN_SUMMARY) {
        return names.join(', ');
      }
      return `${names.slice(0, MAX_SITES_IN_SUMMARY).join(', ')} +${names.length - MAX_SITES_IN_SUMMARY}`;
    },
    deleteWidget(widget: ListedWidget) {
      this.widgetToDelete = widget;
      Matomo.helper.modalConfirm(this.$refs.confirm as HTMLElement, {
        yes: () => {
          CustomWidgetsStore.deleteWidget(widget.id).then(() => {
            NotificationsStore.show({
              message: translate('CustomWidgets_WidgetDeleted'),
              context: 'success',
              id: 'customWidgetsNotification',
              type: 'transient',
            });
          });
        },
      });
    },
  },
});
</script>
