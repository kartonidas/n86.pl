<script>
    import { ref } from 'vue'
    import { useRoute } from 'vue-router'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TemplateForm from './_Form.vue'
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        components: { TemplateForm },
        setup() {
            setMetaTitle('meta.title.documents_template_edit')
            
            const template = ref({
                type : 'agreement',
            })
            
            const documentTemplateService = new DocumentTemplateService()
            
            return {
                documentTemplateService,
                template,
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settings'), disabled : true },
                        {'label' : this.$t('menu.document_templates'), route : { name : 'documents_templates'} },
                        {'label' : this.$t('documents.update_template'), disabled : true },
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
            this.documentTemplateService.get(this.$route.params.templateId)
                .then(
                    (response) => {
                        this.template = response.data
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
            async updateTemplate() {
                this.saving = true
                this.errors = []
                
                this.documentTemplateService.update(this.$route.params.templateId, this.template)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('documents.template_updated'), life: 3000 });
                            this.saving = false;
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(errors)
                            this.saving = false
                        }
                )
            },
        },
    }
</script>


<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('documents.update_template') }}</h4>
        <TemplateForm @submit-form="updateTemplate" :template="template" source="edit" :saving="saving" :loading="loading" :errors="errors" />
    </div>
</template>