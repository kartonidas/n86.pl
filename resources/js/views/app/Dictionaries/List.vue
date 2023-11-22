<script>
    import { ref } from 'vue'
    import { useRouter, useRoute } from 'vue-router'
    import { useI18n } from 'vue-i18n'
    import { useToast } from 'primevue/usetoast';
    import Column from 'primevue/column';
    import DataTable from 'primevue/datatable';
    import Dialog from 'primevue/dialog';
    
    import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
    import DictionaryService from '@/service/DictionaryService'
    
    export default {
        setup() {
            const router = useRouter()
            const route = useRoute()
            const dictionaryService = new DictionaryService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                router,
                route,
                toast,
                t,
                dictionaryService,
            }
        },
        data() {
            return {
                loading: true,
                dictionaries: [],
                type: this.route.params.type,
                displayConfirmation: false,
                deleteDictionaryId: null,
                meta: {
                    currentPage: 1,
                    perPage: 25,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: this.getBreadcrumbs(),
                }
            }
        },
        mounted() {
            this.getList()
        },
        updated() {
            this.type = this.route.params.type
            this.currentPage = 1
        },
        watch: {
            type() {
                this.getList()
                this.meta.breadcrumbItems = this.getBreadcrumbs()
            }
        },
        methods: {
            getList() {
                this.loading = true
                this.dictionaryService.listByType(this.type, this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.dictionaries = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            newDictionary() {
                this.router.push({name: 'dictionary_new', params: {type : this.type}})
            },
            
            editDictionary(dictionaryId) {
                this.router.push({name: 'dictionary_edit', params: { type : this.type, dictionaryId : dictionaryId }})
            },
            
            getBreadcrumbs() {
                let breadcrumbs = [
                    {'label' : this.t('app.settings'), disabled : true },
                    {'label' : this.t('app.dictionaries'), disabled : true },
                ]
                
                switch(this.route.params.type) {
                    case 'bills':
                        breadcrumbs.push({'label' : this.t('app.bill_type'), disabled : true });
                    break;
                    case 'fees':
                        breadcrumbs.push({'label' : this.t('app.fee_include_rent'), disabled : true });
                    break;
                }
                return breadcrumbs;
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteDictionaryId = id
            },
            
            confirmDeleteDictionary() {
                this.dictionaryService.remove(this.deleteDictionaryId)
                    .then(
                        (response) => {
                            this.getList()
                            this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('app.dictionary_deleted'), life: 3000 });
                        },
                        (response) => {
                            this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteDictionaryId = null
            },
            
            closeConfirmation() {
                this.deleteDictionaryId = false
            },
        },
        components: {
            "DataTable": DataTable,
            "Column": Column,
            "Breadcrumb": AppBreadcrumb,
            "Dialog": Dialog,
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="text-right mb-2">
                    <Button :label="$t('app.new_dictionary')" @click="newDictionary" class="text-center"></Button>
                </div>
                
                <DataTable :value="dictionaries" class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading">
                    <Column field="delete" style="min-width: 100px; width: 100px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-pencil" class="p-button p-2 mr-1" style="width: auto" @click="editDictionary(data.id)"/>
                            <Button icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <Column field="name" :header="$t('app.name')" style="min-width: 300px;"></Column>
                        <template #empty>
                            {{ $t('app.dictionary_empty_list') }}
                        </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '350px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteDictionary" class="p-button-text" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>