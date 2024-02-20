<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import NotificationForm from './_Form.vue'
    import UserService from '@/service/UserService'
    
    export default {
        components: { NotificationForm },
        setup() {
            setMetaTitle('meta.title.new_user_notification')
            
            const userService = new UserService()
            return {
                userService,
            }
        },
        data() {
            return {
                errors: [],
                saving: false,
                notification: {},
            }
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.settings'), disabled : true },
                    {'label' : this.$t('menu.my_notifications'), route : { name : 'notifications'} },
                    {'label' : this.$t('notifications.new_user_notification'), disabled : true },
                ]
                
                return items
            },
            
            async createNotification(notification) {
                this.saving = true
                this.errors = []
                
                this.userService.createNotification(notification)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('notifications.notification_added'),
                            });
                            
                            this.$router.push({name: 'notification_edit', params: { notificationId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            
            back() {
                this.$goBack('notifications');
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <NotificationForm @submit-form="createNotification" @back="back" :notification="notification" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>