<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="manageCustomWidgets">
    <WidgetEdit
      v-if="idWidget !== null"
      :key="idWidget"
      :id-widget="idWidget"
    />
    <template v-else>
      <WidgetsList />
      <AllowedDomainsEdit />
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import { Matomo, MatomoUrl } from 'CoreHome';
import WidgetsList from '../WidgetsList/WidgetsList.vue';
import WidgetEdit from '../WidgetEdit/WidgetEdit.vue';
import AllowedDomainsEdit from '../AllowedDomainsEdit/AllowedDomainsEdit.vue';
import CustomWidgetsStore from '../CustomWidgets.store';

export interface ManageWidgetsState {
  // null on the list, 0 when creating a widget
  idWidget: number | null;
}

/**
 * Management page of the custom widgets: list and allowed domains, or the edit form when the URL hash
 * contains idWidget (0 to create a widget).
 */
export default defineComponent({
  name: 'ManageWidgets',
  // the client widget renderer passes widget props (uniqueId, widgetName...) this page does not use
  inheritAttrs: false,
  components: {
    WidgetsList,
    WidgetEdit,
    AllowedDomainsEdit,
  },
  data(): ManageWidgetsState {
    return {
      idWidget: null,
    };
  },
  created() {
    CustomWidgetsStore.fetch();

    watch(() => MatomoUrl.hashParsed.value, () => {
      this.initState();
    });

    this.initState();
  },
  methods: {
    initState() {
      const idWidget = MatomoUrl.hashParsed.value.idWidget as string | undefined;
      const parsed = idWidget === undefined || idWidget === '' ? NaN : parseInt(idWidget, 10);

      this.idWidget = Number.isNaN(parsed) || parsed < 0 ? null : parsed;

      Matomo.helper.lazyScrollToContent();
    },
  },
});
</script>
