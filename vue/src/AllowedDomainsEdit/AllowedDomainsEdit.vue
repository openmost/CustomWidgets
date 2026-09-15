<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    class="customWidgetsAllowedDomains"
    :content-title="translate('CustomWidgets_AllowedDomainsTitle')"
  >
    <p>{{ translate('CustomWidgets_AllowedDomainsDescription') }}</p>
    <div class="customWidgetsAllowedDomains__field">
      <Field
        uicontrol="textarea"
        name="customWidgetsAllowedDomains"
        v-model="domainsText"
        :title="translate('CustomWidgets_DomainsOnePerLine')"
        :full-width="true"
        :disabled="isLoading"
      />
    </div>
    <SaveButton
      :saving="isUpdating"
      :disabled="isLoading"
      @confirm="save()"
    />
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent, watch } from 'vue';
import {
  ContentBlock,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';
import CustomWidgetsStore from '../CustomWidgets.store';

export interface AllowedDomainsEditState {
  domainsText: string;
}

export default defineComponent({
  name: 'AllowedDomainsEdit',
  components: {
    ContentBlock,
    Field,
    SaveButton,
  },
  data(): AllowedDomainsEditState {
    return {
      domainsText: CustomWidgetsStore.allowedDomains.value.join('\n'),
    };
  },
  created() {
    watch(() => CustomWidgetsStore.allowedDomains.value, (domains) => {
      this.domainsText = domains.join('\n');
    });
  },
  computed: {
    isLoading(): boolean {
      return CustomWidgetsStore.isLoading.value;
    },
    isUpdating(): boolean {
      return CustomWidgetsStore.isUpdating.value;
    },
  },
  methods: {
    save() {
      const domains = this.domainsText.split(/[\s,]+/).filter((domain) => domain !== '');

      CustomWidgetsStore.saveAllowedDomains(domains).then(() => {
        NotificationsStore.show({
          message: translate('CustomWidgets_AllowedDomainsSaved'),
          context: 'success',
          id: 'customWidgetsNotification',
          type: 'transient',
        });
      });
    },
  },
});
</script>
