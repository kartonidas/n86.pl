<script>
    import { ref } from 'vue'
    import { useRoute } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, maxLength } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Editor from 'primevue/editor';
    import EditorToolbar from './../_partials/EditorToolbar.vue'
    
    import DocumentService from '@/service/DocumentService'
    
    export default {
        components: { Editor, EditorToolbar },
        setup() {
            setMetaTitle('meta.title.documents_edit')
            
            const documentService = new DocumentService()
            
            return {
                v: useVuelidate(),
                documentService,
            }
        },
        data() {
            return {
                document: {},
                saving: false,
                loading: true,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.documents'), route : { name : 'documents'} },
                        {'label' : this.$t('documents.update_document'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.documentService.get(this.$route.params.documentId)
                .then(
                    (response) => {
                        this.document = response.data
                        this.loading = false
                    },
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async submitForm() {
                this.saving = true
                this.errors = []
                
                const result = await this.v.$validate()
                if (result) {
                    this.documentService.update(this.$route.params.documentId, this.document)
                        .then(
                            (response) => {
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('documents.document_updated'), life: 3000 });
                                this.saving = false;
                            },
                            (errors) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(errors)
                                this.saving = false
                            }
                        )
                }
            },
            back() {
                this.$goBack('documents');
            }
        },
        validations () {
            return {
                document: {
                    title: { required, maxLengthValue: maxLength(200) },
                    content: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="card p-fluid mt-4 pt-4">
        <Help show="rental:document,template" mark="rental:document" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('documents.update_document') }}</h4>
        <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
            <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                <ul class="list-unstyled">
                    <li v-for="error of errors">
                        {{ error }}
                    </li>
                </ul>
            </Message>
            <div class="mb-4">
                <div class="p-fluid">
                    <div class="formgrid grid">
                        <div class="field col-12 md:col-12 mb-4">
                            <label for="title" v-required class="block text-900 font-medium mb-2">{{ $t('documents.title') }}</label>
                            <InputText id="title" type="text" :placeholder="$t('documents.title')" class="w-full" :class="{'p-invalid' : v.document.title.$error}" v-model="document.title" :disabled="saving || loading"/>
                            <div v-if="v.document.title.$error">
                                <small class="p-error">{{ v.document.title.$errors[0].$message }}</small>
                            </div>
                        </div>
                        
                        <div class="field col-12 mb-4">
                            <label for="content" v-required class="block text-900 font-medium mb-2">{{ $t('documents.content') }}</label>
                            <Editor v-model="document.content" editorStyle="height: 320px">
                                <template v-slot:toolbar>
                                    <EditorToolbar/>
                                </template>
                            </Editor>
                            <div v-if="v.document.content.$error">
                                <small class="p-error">{{ v.document.content.$errors[0].$message }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-footer">
                <div v-if="loading">
                    <ProgressSpinner style="width: 25px; height: 25px"/>
                </div>
                
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
        </form>
    </div>
</template>