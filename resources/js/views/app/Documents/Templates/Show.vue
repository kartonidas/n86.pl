<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle, hasAccess, getValueLabel } from '@/utils/helper'
    
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.items_show_bill')
            
            const documentTemplateService = new DocumentTemplateService()
            return {
                documentTemplateService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                template : {},
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
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
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.settings'), disabled : true },
                    {'label' : this.$t('menu.document_templates'), route : { name : 'documents_templates'} },
                    {'label' : this.$t('documents.show_template'), disabled : true },
                ]
                return items
            },
            editTemplate() {
                this.$router.push({name: 'documents_templates_edit'})
            },
            getType() {
                return getValueLabel("documents.types", this.template.type)
            },
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex align-items-center mb-3">
                    <div class="w-full">
                        <Badge :value="getType()" class="font-normal" severity="info"></Badge>
                        <h3 class="mt-2 mb-1 text-color">{{ template.title }}</h3>
                    </div>
                    <div class="text-right" v-if="hasAccess('config:update')">
                        <Button icon="pi pi-pencil" @click="editTemplate" v-tooltip.left="$t('app.edit')"></Button>
                    </div>
                </div>
                
                <div v-html="template.content"></div>
            </div>
        </div>
    </div>
</template>