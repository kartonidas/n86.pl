<script>
    import { hasAccess, getValues, getValueLabel, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.documents_template_list')
            
            const documentTemplateService = new DocumentTemplateService()
            
            return {
                documentTemplateService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                templates: [],
                displayConfirmation: false,
                deleteTemplateId: null,
                template_types: getValues('documents.types'),
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-document-templates-table"),
                        size: this.rowsPerPage,
                        sort: 'title',
                        order: 1,
                    },
                    search: {},
                    loading: false,
                    totalRecords: null,
                    totalPages: null,
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.getList()
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.settings'), disabled : true },
                    {'label' : this.$t('menu.document_templates'), disabled : true },
                ]
                return items
            },
            
            getList() {
                this.meta.loading = true
                
                this.documentTemplateService.list(this.meta.list, this.meta.search)
                    .then(
                        (response) => {
                            this.templates = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newTemplate() {
                this.$router.push({name: 'documents_templates_new'})
            },
            
            showTemplate(templateId) {
                this.$router.push({name: 'documents_templates_show', params: { templateId : templateId }})
            },
            
            changePage(event) {
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteTemplateId = id
            },
            
            confirmDeleteTemplate() {
                this.documentTemplateService.remove(this.deleteTemplateId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('documents.template_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteBillId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                this.showTemplate(event.data.id)
            },
            
            search() {
                this.meta.list.first = 0
                this.getList()
            },
            
            sort(event) {
                this.meta.list.sort = event['sortField']
                this.meta.list.order = event['sortOrder']
                this.meta.list.first = 0
                
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                this.getList()
            }
        },
    }
</script>
<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
     <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="rental:document,template" mark="rental:template" class="text-right mb-3"/>
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.document_templates') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('config:update')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('documents.add_template')" @click="newTemplate" class="text-center"></Button>
                    </div>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-5 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="template_types" optionLabel="name" optionValue="id" :placeholder="$t('documents.template_type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-5 mb-3">
                            <InputText type="text" :placeholder="$t('documents.template_title')" class="w-full" v-model="meta.search.title"/>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :value="templates" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="this.meta.list.first" @sort="sort($event)" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)" :sortField="this.meta.list.sort" :sortOrder="this.meta.list.order" stateStorage="session" stateKey="dt-state-document-templates-table">
                    <Column field="type" :header="$t('documents.template_type')">
                        <template #body="{ data }">
                            {{ getValueLabel('documents.types', data.type) }}
                        </template>
                    </Column>
                    <Column field="title" sortable :header="$t('documents.template_title')" style="min-width: 300px;"></Column>
                    <Column field="delete" v-if="hasAccess('config:update')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button v-tooltip.bottom="$t('app.remove')" icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('documents.template_empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteTemplate" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>