<script>
    import { ref } from 'vue'
    import { useRoute } from 'vue-router'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TenantForm from './_Form.vue'
    import TenantService from '@/service/TenantService'
    
    export default {
        components: { TenantForm },
        setup() {
            setMetaTitle('meta.title.tenants_edit')
            
            const tenant = ref({
                type : 'person',
                contacts : {
                    email : [],
                    phone : []
                },
            })
            
            const route = useRoute()
            const tenantService = new TenantService()
            
            return {
                tenantService,
                tenant,
                route
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.tenant_list'), route : { name : 'tenants'} },
                        {'label' : this.$t('tenants.card'), route : { name : 'tenant_show'} },
                        {'label' : this.$t('tenants.edit_tenant'), disabled : true },
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
            this.tenantService.get(this.route.params.tenantId)
                .then(
                    (response) => {
                        this.tenant = response.data
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
            async updateTenant() {
                this.saving = true
                this.errors = []
                
                this.tenantService.update(this.route.params.tenantId, this.tenant)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('tenants.updated'), life: 3000 });
                            this.saving = false;
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(errors)
                            this.saving = false
                        }
                )
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="tenant_customer" mark="tenant_customer:tenant" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('tenants.basic_data') }}</h4>
        <TenantForm @submit-form="updateTenant" :tenant="tenant" source="edit" :saving="saving" :loading="loading" :errors="errors" />
    </div>
</template>