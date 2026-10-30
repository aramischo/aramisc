<template>
    <div class="quillWrapper">
        <div ref="quillContainer"></div>
    </div>
</template>

<script>
// Vue 3 drop-in for the "vue2-editor" component : same v-model (HTML string),
// same "editor-toolbar" prop and the Quill instance exposed as `.quill`.
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const EMPTY = '<p><br></p>';

export default {
    name: "VueEditor",

    props: {
        modelValue: {
            type: String,
            default: ''
        },
        editorToolbar: {
            type: Array,
            default: () => []
        },
        placeholder: {
            type: String,
            default: ''
        }
    },

    emits: ['update:modelValue'],

    mounted() {
        // Kept out of data() : a reactive proxy around Quill breaks it.
        this.quill = new Quill(this.$refs.quillContainer, {
            theme: 'snow',
            placeholder: this.placeholder,
            modules: {
                toolbar: this.editorToolbar.length ? this.editorToolbar : true
            }
        });

        this.setContent(this.modelValue);

        this.quill.on('text-change', () => {
            const html = this.quill.root.innerHTML;
            this.$emit('update:modelValue', html === EMPTY ? '' : html);
        });
    },

    watch: {
        modelValue(value) {
            if (this.quill && (value || '') !== (this.quill.root.innerHTML === EMPTY ? '' : this.quill.root.innerHTML)) {
                this.setContent(value);
            }
        }
    },

    methods: {
        setContent(value) {
            if (value) {
                this.quill.clipboard.dangerouslyPasteHTML(value, 'silent');
            } else {
                this.quill.setText('', 'silent');
            }
        }
    },

    beforeUnmount() {
        this.quill && this.quill.off('text-change');
    }
}
</script>
