<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import PermissionService from '@/service/PermissionService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.permissions_list')
            
            const router = useRouter()
            const permissionService = new PermissionService()
            
            return {
                router,
                permissionService,
                hasAccess
            }
        },
        data() {
            return {
                loading: false,
                permissions: [],
                displayConfirmation: false,
                deletePermissionId: null,
                meta: {
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.users'), disabled : true },
                        {'label' : this.$t('menu.permissions'), route : { name : 'permissions'}, disabled : true },
                    ],
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
        methods: {
            getList() {
                this.loading = true
                this.permissionService.list(this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.permissions = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        }
                    )
            },
            
            newGroup() {
                this.router.push({name: 'permission_new'})
            },
            
            editGroup(permissionId) {
                this.router.push({name: 'permission_edit', params: { permissionId : permissionId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deletePermissionId = id
            },
            
            confirmDeleteGroup() {
                this.permissionService.remove(this.deletePermissionId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('permissions.deleted'), life: 3000 });
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                        },
                    )
                
                this.displayConfirmation = false
                this.deletePermissionId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            rowClick(event) {
                if (hasAccess('permission:update')) 
                    this.editGroup(event.data.id)
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.permissions') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('permission:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('permissions.add_group')" @click="newGroup" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="permissions" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                     <Column :header="$t('permissions.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <router-link :to="{name: 'permission_edit', params: { permissionId : data.id }}" v-if="hasAccess('permission:update')">
                                {{ data.name }}
                            </router-link>
                            <span v-else>
                                {{ data.name }}
                            </span>
                        </template>
                     </Column>
                     <Column field="is_default" :header="$t('permissions.default')" style="min-width: 100px; width: 100px" class="text-center">
                        <template #body="{ data }">
                            <span v-if="data.is_default">{{ $t('app.yes') }}</span>
                        </template>
                     </Column>
                     <Column field="delete" v-if="hasAccess('permission:delete')" style="min-width: 45px; width: 45px" class="text-center">
                        <template #body="{ data }">
                            <Button v-if="hasAccess('permission:delete')" v-tooltip.bottom="$t('app.remove')" icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)" :disabled="!data.can_delete"/>
                        </template>
                    </Column>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteGroup" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>