<template>
    <div ref="editorRef"></div>
    <!-- Indikator jumlah karakter -->
    <div class="text-end mt-1">
        <small
            :style="{
                color: charCount >= maxLength ? 'red' : '#6c757d',
                fontWeight: charCount >= maxLength ? 'bold' : 'normal',
            }"
        >
            {{ charCount }} / {{ maxLength }}
        </small>
    </div>
</template>

<script setup>
import {
    ref,
    watch,
    onMounted,
    onUnmounted,
    defineProps,
    defineEmits,
} from "vue";
import "summernote/dist/summernote-lite";
import "summernote/dist/summernote-lite.css";
const props = defineProps({
    modelValue: String,
    maxLength: {
        type: Number,
        default: 250,
    },
    options: {
        type: Object,
        default: () => ({
            placeholder: "",
            tabsize: 2,
            height: 200,
        }),
    },
});

const emit = defineEmits(["update:modelValue", "update:charCount"]);
const editorRef = ref(null);
const charCount = ref(0);
let isInitialized = false;

// Fungsi pembatas isi teks
const limitContent = (html) => {
    const textOnly = $("<div>").html(html).text();
    if (textOnly.length > props.maxLength) {
        const truncatedText = textOnly.substring(0, props.maxLength);
        console.log(truncatedText);
        return $("<div>").text(truncatedText).html();
    }
    return html;
};

const updateCount = (html) => {
    charCount.value = $("<div>").html(html).text().length;
    emit("update:charCount", charCount.value);
};

const initializeSummernote = () => {
    if (isInitialized) return;

    $(editorRef.value).summernote({
        ...props.options,
        callbacks: {
            onInit: function () {
                $(editorRef.value).summernote("code", props.modelValue || "");
                isInitialized = true;
                updateCount(props.modelValue || "");
            },

            onChange: function (contents) {
                let limitedContent = limitContent(contents);
                if (limitedContent !== contents) {
                    $(editorRef.value).summernote("code", limitedContent);
                    contents = limitedContent;
                }

                emit("update:modelValue", contents);
                updateCount(contents);
            },

            onPaste: function (e) {
                e.preventDefault();
                const clipboardData = (
                    e.originalEvent || e
                ).clipboardData.getData("text");
                const currentText = $("<div>")
                    .html($(editorRef.value).summernote("code"))
                    .text();
                const remaining = props.maxLength - currentText.length;

                let insertText = clipboardData.substring(0, remaining);
                $(editorRef.value).summernote(
                    "pasteHTML",
                    $("<div>").text(insertText).html()
                );
            },

            onKeyup: function () {
                const contents = $(editorRef.value).summernote("code");
                const limitedContent = limitContent(contents);
                if (limitedContent !== contents) {
                    $(editorRef.value).summernote("code", limitedContent);
                }
                updateCount(limitedContent);
            },
        },
    });
};

// Sinkronisasi dari parent
watch(
    () => props.modelValue,
    (newValue) => {
        const currentCode = $(editorRef.value).summernote("code");
        if (isInitialized && currentCode !== newValue) {
            $(editorRef.value).summernote("code", newValue);
            updateCount(newValue);
        }
    }
);

onMounted(() => {
    initializeSummernote();
});

onUnmounted(() => {
    if (isInitialized) {
        $(editorRef.value).summernote("destroy");
        isInitialized = false;
    }
});
</script>

<style scoped>
.text-end {
    text-align: right;
}
</style>
