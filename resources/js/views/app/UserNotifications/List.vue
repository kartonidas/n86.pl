<script>
    import { ref } from 'vue'
    import { setMetaTitle, getValueLabel } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import UserService from '@/service/UserService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.my_notifications')
            
            const userService = new UserService()
            
            return {
                userService,
                getValueLabel
            }
        },
        data() {
            return {
                loading: false,
                errors: [],
                notifications: [],
                displayConfirmation: false,
                deleteNotificationId: null,
                meta: {
                    search: {},
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    totalRecords: null,
                    totalPages: null,
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settings'), disabled : true },
                        {'label' : this.$t('menu.my_notifications'), route : { name : 'notifications'} },
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
                this.loading = true
                
                this.userService.notifications(this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.notifications = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            newNotification() {
                this.$router.push({name: 'notification_new'})
            },
            
            editNotification(notificationId) {
                this.$router.push({name: 'notification_edit', params: { notificationId : notificationId }})
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getList()
            },
            
            rowClick(event) {
                this.editNotification(event.data.id)
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteNotificationId = id
            },
            
            confirmDeleteNotification() {
                this.userService.removeNotification(this.deleteNotificationId)
                    .then(
                        (response) => {
                            this.getList()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('notifications.deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteNotificationId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h4 class="inline-flex mb-0 text-color font-medium">{{ $t('menu.my_notifications') }}</h4>
                    <div class="text-right mb-0 inline-flex">
                        <Button icon="pi pi-plus" v-tooltip.left="$t('notifications.add_notification')" @click="newNotification" class="text-center"></Button>
                    </div>
                </div>
                <DataTable :value="notifications" stripedRows class="p-datatable-gridlines clickable" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="loading" @row-click="rowClick($event)">
                    <Column :header="$t('notifications.type')" field="type">
                        <template #body="{ data }">
                            {{ getValueLabel("notifications.types", data.type) }}
                        </template>
                    </Column>
                    <Column :header="$t('notifications.days')" field="days" class="text-center"></Column>
                    <Column :header="$t('notifications.mode')" field="mode">
                        <template #body="{ data }">
                            {{ getValueLabel("notifications.modes", data.mode) }}
                        </template>
                    </Column>
                    <Column field="delete" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('faults.empty_list') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteNotification" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>