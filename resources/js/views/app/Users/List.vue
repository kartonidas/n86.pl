<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import UsersService from '@/service/UsersService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.users_list')
            
            const router = useRouter()
            const usersService = new UsersService()
            
            return {
                router,
                usersService,
                hasAccess
            }
        },
        data() {
            return {
                loading: false,
                users: [],
                displayConfirmation: false,
                deleteUserId: null,
                meta: {
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.users'), disabled : true },
                        {'label' : this.$t('menu.users_list'), disabled : true },
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
                this.usersService.list(this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.users = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newUser() {
                this.router.push({name: 'user_new'})
            },
            
            editUser(userId) {
                this.router.push({name: 'user_edit', params: { userId : userId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteUserId = id
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            confirmDeleteUser() {
                this.usersService.remove(this.deleteUserId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('users.deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteUserId = null
            },
            
            rowClick(event) {
                if (hasAccess('user:update')) 
                    this.editUser(event.data.id)
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
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.users_list') }}</h4>
                    <div class="text-right mb-0 inline-flex" v-if="hasAccess('user:create')">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('users.add_user')" @click="newUser" class="text-center"></Button>
                    </div>
                </div>
                
                <DataTable :value="users" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column field="name" :header="$t('users.name')" style="min-width: 300px;">
                        <template #body="{ data }">
                            <router-link :to="{name: 'user_edit', params: { userId : data.id }}" v-if="hasAccess('user:update')">
                                {{ data.firstname }} {{ data.lastname }}
                            </router-link>
                            <span v-else>
                                {{ data.firstname }} {{ data.lastname }}
                            </span>
                        </template>
                    </Column>
                    <Column field="email" :header="$t('users.email')"></Column>
                    <Column field="phone" :header="$t('users.phone')"></Column>
                    <Column :header="$t('users.permission_group')">
                        <template #body="{ data }">
                            <span v-if="data.owner">{{ $t('users.owner') }}</span>
                            <span v-if="!data.owner && data.superuser">{{ $t('users.superuser') }}</span>
                            <span v-if="!data.owner && !data.superuser">{{ data.user_permission_name }}</span>
                        </template>
                    </Column>
                    <Column v-if="hasAccess('user:delete')" field="delete" style="min-width: 45px; width: 45px" class="text-center">
                        <template #body="{ data }">
                            <Button v-tooltip.bottom="$t('app.remove')" icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)" :disabled="!data.can_delete"/>
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
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteUser" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>