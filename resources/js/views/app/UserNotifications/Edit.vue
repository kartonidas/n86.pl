<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import NotificationForm from './_Form.vue'
    import UserService from '@/service/UserService'
    
    export default {
        components: { NotificationForm },
        setup() {
            setMetaTitle('meta.title.update_fault')
            
            const userService = new UserService()
            return {
                userService,
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                notification : {},
                saving: false,
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.userService.getNotification(this.$route.params.notificationId)
                .then(
                    (response) => {
                        this.notification = response.data
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
                    {'label' : this.$t('menu.my_notifications'), route : { name : 'notifications'} },
                    {'label' : this.$t('notifications.edit_user_notification'), disabled : true },
                ]
                    
                return items
            },
            
            async updateNotification(notification) {
                this.saving = true
                this.errors = []
                
                this.userService.updateNotification(this.$route.params.notificationId, notification)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('notifications.notification_updated'), life: 3000 });
                            this.saving = false;
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            
            back() {
                this.$goBack('notifications', true);
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <NotificationForm @submit-form="updateNotification" @back="back" :notification="notification" source="update" :saving="saving" :loading="loading" :errors="errors" />
            </div>
        </div>
    </div>
</template>