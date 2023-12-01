<script>
    import { ref, reactive, computed } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import TenantService from '@/service/TenantService'
    import TenantForm from './_Form.vue'
    
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
            const state = reactive({ 'tenant' : tenant})
            const rules = computed(() => {
                const rules = {
                    tenant: {
                        name: { required },
                        type: { required },
                    }
                }
                
                rules.tenant.contacts = {}
                if(state.tenant.contacts.email.length)
                {
                    rules.tenant.contacts.email = []
                    
                    for(var i = 0; i < state.tenant.contacts.email.length; i++)
                        rules.tenant.contacts.email.push({ val : { required, email } })
                }
                
                if(state.tenant.contacts.phone.length)
                {
                    rules.tenant.contacts.phone = []
                    
                    for(var i = 0; i < state.tenant.contacts.phone.length; i++)
                        rules.tenant.contacts.phone.push({ val : { required } })
                }
                
                return rules
            })
            
            const router = useRouter()
            const tenantService = new TenantService()
            
            return {
                v$: useVuelidate(rules, state),
                tenantService,
                router,
                tenant
            }
        },
        data() {
            return {
                types: this.tenantService.types(this.$t),
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
                const result = await this.v$.$validate()
                if (result) {
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
                                
                                this.router.push({name: 'tenant_show', params: { tenantId : response.data }})
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        )
                }
            },
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2">{{ $t('tenants.basic_data') }}</h4>
        <form v-on:submit.prevent="createTenant">
            <TenantForm :tenant="tenant" :types="types" :saving="saving" :errors="errors" :v="v$" />
            
            <div class="text-right">
                <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
            </div>
        </form>
    </div>
</template>