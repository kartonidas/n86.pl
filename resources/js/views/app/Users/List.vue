<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useI18n } from 'vue-i18n'
    
    import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
    import Column from 'primevue/column';
    import DataTable from 'primevue/datatable';
    import Dialog from 'primevue/dialog';
    import UsersService from '@/service/UsersService'
    
    export default {
        setup() {
            const router = useRouter()
            const usersService = new UsersService()
            const { t } = useI18n();
            
            return {
                router,
                t,
                usersService,
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
                    perPage: 25,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.t('app.users'), route : { name : 'users'}, disabled : true },
                    ],
                }
            }
        },
        mounted() {
            this.getList()
        },
        methods: {
            getList() {
                this.usersService.list(this.meta.perPage, this.meta.currentPage)
                    .then(response => {
                        this.users = response.data.data
                        this.meta.totalRecords = response.data.total_rows
                        this.meta.totalPages = response.data.total_pages
                    }, () => {});
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
                    .then((response) => {
                        this.getList()
                    })
                
                this.displayConfirmation = false
                this.deleteUserId = null
            }
        },
        components: {
            "DataTable": DataTable,
            "Column": Column,
            "Dialog": Dialog,
            "Breadcrumb": AppBreadcrumb,
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="text-right mb-2">
                    <Button :label="$t('app.add_new_user')" @click="newUser" class="text-center"></Button>
                </div>
                
                <DataTable :value="users" class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage">
                    <Column field="delete">
                        <template #body="{ data }">
                            <Button icon="pi pi-pencil" class="p-button p-2 mr-1" style="width: auto" @click="editUser(data.id)" />
                            <Button icon="pi pi-trash" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)" />
                        </template>
                    </Column>
                    <Column field="name" header="Name">
                        <template #body="{ data }">
                            {{ data.firstname }} {{ data.lastname }}
                        </template>
                    </Column>
                    <Column field="email" :header="$t('auth.email')"></Column>
                    <Column field="phone" :header="$t('auth.phone')"></Column>
                </DataTable>
                <Dialog header="Confirmation" v-model:visible="displayConfirmation" :style="{ width: '350px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>Are you sure you want to proceed?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button label="Yes" icon="pi pi-check" @click="confirmDeleteUser" class="p-button-text" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>