<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="container"
    class="customWidgetsCodeEditor"
  />
</template>

<script lang="ts">
import {
  defineComponent,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from 'vue';
import { basicSetup, EditorView } from 'codemirror';
import { EditorState } from '@codemirror/state';
import { tooltips } from '@codemirror/view';
import { html } from '@codemirror/lang-html';
import { oneDark } from '@codemirror/theme-one-dark';

/**
 * CodeMirror editor highlighting HTML with its inline JavaScript and CSS, bound with v-model.
 */
export default defineComponent({
  name: 'HtmlCodeEditor',
  props: {
    modelValue: {
      type: String,
      default: '',
    },
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const container = ref<HTMLElement | null>(null);
    let view: EditorView | null = null;

    onMounted(() => {
      view = new EditorView({
        parent: container.value!,
        state: EditorState.create({
          doc: props.modelValue,
          extensions: [
            basicSetup,
            html(),
            oneDark,
            EditorView.lineWrapping,
            // the editor clips its overflow (rounded corners): render autocomplete tooltips in the body
            tooltips({ parent: document.body }),
            EditorView.contentAttributes.of({ spellcheck: 'false' }),
            EditorView.updateListener.of((update) => {
              if (update.docChanged) {
                emit('update:modelValue', update.state.doc.toString());
              }
            }),
          ],
        }),
      });
    });

    watch(() => props.modelValue, (value) => {
      const text = value || '';
      if (view && text !== view.state.doc.toString()) {
        view.dispatch({ changes: { from: 0, to: view.state.doc.length, insert: text } });
      }
    });

    onBeforeUnmount(() => {
      view?.destroy();
      view = null;
    });

    return {
      container,
    };
  },
});
</script>
