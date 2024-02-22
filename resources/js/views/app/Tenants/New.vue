<script>
    import { ref } from 'vue'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TenantForm from './_Form.vue'
    import TenantService from '@/service/TenantService'
    
    export default {
        components: { TenantForm },
        setup() {
            setMetaTitle('meta.title.tenants_new')
            
            const tenant = ref({
                type : 'person',
                contacts : {
                    email : [],
                    phone : []
                },
                country : 'PL'
            })
            
            const tenantService = new TenantService()
            
            return {
                tenantService,
                tenant
            }
        },
        data() {
            return {
                saving: false,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.tenant_list'), route : { name : 'tenants'} },
                        {'label' : this.$t('tenants.new_tenant'), disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createTenant() {
                this.saving = true
                this.errors = []
                
                this.tenantService.create(this.tenant)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('tenants.added'),
                            });
                            
                            this.$router.push({name: 'tenant_show', params: { tenantId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4 pt-4">
        <Help show="tenant_customer" mark="tenant_customer:tenant" class="text-right mb-3"/>
        <h4 class="mb-5 header-border-bottom pb-2 text-color">{{ $t('tenants.basic_data') }}</h4>
        <TenantForm @submit-form="createTenant" :tenant="tenant" :saving="saving" :errors="errors" />
    </div>
</template>