<script>
    import { ref } from 'vue'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TemplateForm from './_Form.vue'
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        components: { TemplateForm },
        setup() {
            setMetaTitle('meta.title.documents_template_new')
            
            const template = ref({
                type : 'agreement',
            })
            
            const documentTemplateService = new DocumentTemplateService()
            
            return {
                documentTemplateService,
                template
            }
        },
        data() {
            return {
                saving: false,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settings'), disabled : true },
                        {'label' : this.$t('menu.document_templates'), route : { name : 'documents_templates'} },
                        {'label' : this.$t('documents.new_template'), disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createTemplate() {
                this.saving = true
                this.errors = []
                
                this.documentTemplateService.create(this.template)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('documents.template_added'),
                            });
                            
                            this.$router.push({name: 'documents_templates_show', params: { templateId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="rental:document,template" mark="rental:template" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('documents.new_template') }}</h4>
        <TemplateForm @submit-form="createTemplate" :template="template" :saving="saving" :errors="errors" />
    </div>
</template>
