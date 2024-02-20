<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import FaultForm from './_Form.vue'
    import FaultService from '@/service/FaultService'
    
    export default {
        components: { FaultForm },
        setup() {
            setMetaTitle('meta.title.update_fault')
            
            const faultService = new FaultService()
            return {
                faultService,
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                fault : {},
                saving: false,
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.faultService.get(this.$route.params.faultId)
                .then(
                    (response) => {
                        this.fault = response.data
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
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.faults'), route : { name : 'faults'} },
                    {'label' : this.$t('faults.edit_fault'), disabled : true }
                ]
                    
                return items
            },
            
            async updateFault(fault) {
                this.saving = true
                this.errors = []
                
                this.faultService.update(this.$route.params.faultId, fault)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('faults.fault_updated'), life: 3000 });
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
                this.$goBack('fault_show', true);
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <FaultForm @submit-form="updateFault" @back="back" :fault="fault" source="update" :saving="saving" :loading="loading" :errors="errors" />
            </div>
        </div>
    </div>
</template>