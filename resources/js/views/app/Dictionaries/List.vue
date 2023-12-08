<script>
    import { ref } from 'vue'
    import { useRouter, useRoute } from 'vue-router'
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import DictionaryService from '@/service/DictionaryService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.dictionaries_list')
            
            const router = useRouter()
            const route = useRoute()
            const dictionaryService = new DictionaryService()
            
            return {
                router,
                route,
                dictionaryService,
                hasAccess
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
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: this.getBreadcrumbs(),
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
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
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
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
                    {'label' : this.$t('menu.settings'), disabled : true },
                    {'label' : this.$t('menu.dictionaries'), disabled : true },
                ]
                
                switch(this.route.params.type) {
                    case 'bills':
                        breadcrumbs.push({'label' : this.$t('menu.bill_type'), disabled : true });
                    break;
                    case 'fees':
                        breadcrumbs.push({'label' : this.$t('menu.fee_include_rent'), disabled : true });
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
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('dictionaries.deleted'), life: 3000 });
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteDictionaryId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                if (hasAccess('dictionary:update')) 
                    this.editDictionary(event.data.id)
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.dictionaries') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('dictionary:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('dictionaries.new_value')" @click="newDictionary" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="dictionaries" stripedRows class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column :header="$t('dictionaries.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <router-link :to="{name: 'dictionary_edit', params: { type : data.type, dictionaryId : data.id }}" v-if="hasAccess('dictionary:update')">
                                {{ data.name }}
                            </router-link>
                            <span v-else>
                                {{ data.name }}
                            </span>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('dictionary:delete')" style="min-width: 45px; width: 45px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('dictionaries.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteDictionary" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>