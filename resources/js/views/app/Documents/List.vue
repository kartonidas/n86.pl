<script>
    import { appStore } from '@/store.js'
    import { hasAccess, setMetaTitle, getValues, getValueLabel } from '@/utils/helper'
    
    import DocumentService from '@/service/DocumentService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.documents_list')
            
            const documentService = new DocumentService()
            
            return {
                documentService,
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                documents: [],
                types: getValues('documents.types'),
                displayDocumentConfirmation: false,
                deleteDocumentId: null,
                meta: {
                    list: {
                        first: appStore().getDTSessionStateFirst("dt-state-documents-table"),
                        size: this.rowsPerPage,
                        sort: 'title',
                        order: 1,
                    },
                    search: {},
                    loading: false,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.documents'), disabled : true },
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
            this.getList()
        },
        methods: {
            getList() {
                this.meta.loading = true
                
                this.documentService.list(this.meta.list, this.meta.search)
                    .then(
                        (response) => {
                            this.documents = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            search() {
                this.meta.list.first = 0
                this.getList()
            },
            
            resetSearch() {
                this.meta.list.first = 0
                this.meta.search = {}
                this.getList()
            },
            
            changePage(event) {
                this.meta.list.first = event["first"];
                this.getList()
            },
            
            sort(event) {
                this.meta.list.sort = event['sortField']
                this.meta.list.order = event['sortOrder']
                this.meta.list.first = 0
                this.getList()
            },
            
            downloadPDF(documentId) {
                this.documentService.getPDFDocument(documentId)
                    .then(
                        (response) => {
                            const contentDisposition = response.headers['content-disposition'];
                            let fileName = 'file.pdf';
                            if (contentDisposition) {
                                const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                                if (fileNameMatch.length === 2)
                                    fileName = fileNameMatch[1];
                            }
                            
                            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                            var fileLink = document.createElement('a');
                            fileLink.href = fileURL;
                            fileLink.setAttribute('download', fileName);
                            document.body.appendChild(fileLink);
                            fileLink.click();
                        },
                        (errors) => {
                            
                        }
                    )
            },
            
            openDocumentConfirmation(id) {
                this.displayDocumentConfirmation = true
                this.deleteDocumentId = id
            },
            
            confirmDeleteDocument() {
                this.documentService.remove(this.deleteDocumentId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('documents.document_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayDocumentConfirmation = false
                this.deleteDocumentId = null
            },
            
            closeDocumentConfirmation() {
                this.displayDocumentConfirmation = false
            },
            
            rowClick(event) {
                if (hasAccess('document:update')) 
                    this.$router.push({name: 'document_edit', params: {documentId : event.data.id}})
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.documents') }}</h4>
                </div>
                
                <form v-on:submit.prevent="search">
                    <div class="formgrid grid mb-1">
                        <div class="col-12 md:col-3 mb-3">
                            <Dropdown v-model="meta.search.type" :showClear="this.meta.search.type ? true : false" :options="types" optionLabel="name" optionValue="id" :placeholder="$t('documents.type')" class="w-full" />
                        </div>
                        
                        <div class="col-12 md:col-4 mb-3">
                            <InputText type="text" :placeholder="$t('documents.title')" class="w-full" v-model="meta.search.title"/>
                        </div>
                        
                        <div class="col-12 md:col-3 mb-3">
                            <InputText type="text" :placeholder="$t('documents.item_name')" class="w-full" v-model="meta.search.item_name"/>
                        </div>
                        
                        <div class="col-12 mb-3" style="width: 120px;">
                            <Button type="submit" iconPos="left" icon="pi pi-search" class="mr-2"/>
                            <Button severity="secondary" outlined iconPos="left" icon="pi pi-filter-slash" @click="resetSearch"/>
                        </div>
                    </div>
                </form>
                
                <DataTable :value="documents" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.list.size" :first="meta.list.first" @sort="sort($event)" @page="changePage" :loading="meta.loading" @row-click="rowClick($event)" :sortField="this.meta.list.sort" :sortOrder="this.meta.list.order" stateStorage="session" stateKey="dt-state-documents-table">
                    <Column :header="$t('documents.estate')">
                        <template #body="{ data }">
                            <span v-if="data.item">
                                <router-link :to="{name: 'item_show', params: { itemId : data.item.id }}" v-if="hasAccess('customer:update')">
                                    {{ data.item.name }}
                                </router-link>
                            </span>
                        </template>
                    </Column>
                    <Column :header="$t('documents.rental')">
                        <template #body="{ data }">
                            <span v-if="data.rental">
                                <router-link :to="{name: 'rental_show', params: { rentalId : data.rental.id }}" v-if="hasAccess('customer:update')">
                                    {{ data.rental.full_number}}
                                </router-link>
                            </span>
                        </template>
                    </Column>
                    <Column field="title" sortable :header="$t('documents.title')" style="min-width: 300px;"></Column>
                    <Column field="type" :header="$t('documents.type')">
                        <template #body="{ data }">
                            {{ getValueLabel('documents.types', data.type) }}
                        </template>
                    </Column>
                    <Column class="text-center" style="min-width: 60px; width: 60px;">
                        <template #body="{ data }">
                            <Button icon="pi pi-file-pdf" v-tooltip.bottom="$t('rent.download_document')" class="p-button-info p-2" style="width: auto" @click="downloadPDF(data.id)"/>
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('document:delete')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openDocumentConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('documents.empty_list') }}
                    </template>
                </DataTable>
                
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayDocumentConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeDocumentConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteDocument" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>