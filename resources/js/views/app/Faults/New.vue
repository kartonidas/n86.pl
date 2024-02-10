<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import FaultForm from './_Form.vue'
    import FaultService from '@/service/FaultService'
    
    export default {
        components: { FaultForm },
        setup() {
            setMetaTitle('meta.title.new_fault')
            
            const faultService = new FaultService()
            return {
                faultService,
            }
        },
        data() {
            return {
                errors: [],
                saving: false,
                fault: {
                    priority: "normal"
                },
            }
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.faults'), route : { name : 'faults'} },
                    {'label' : this.$t('faults.new_fault'), disabled : true },
                ]
                
                return items
            },
            
            async createFault(fault) {
                this.saving = true
                this.errors = []
                
                this.faultService.create(fault)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('faults.fault_added'),
                            });
                            
                            this.$router.push({name: 'fault_show', params: { faultId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            
            back() {
                this.$router.push({name: 'faults'})
            }
        }
    }
</script>


<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <FaultForm @submit-form="createFault" @back="back" :fault="fault" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>