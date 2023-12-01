<script>
    import { ref, reactive, computed } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'
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
            
            const route = useRoute()
            const router = useRouter()
            const tenantService = new TenantService()
            const toast = useToast();
            
            return {
                v$: useVuelidate(rules, state),
                tenantService,
                route,
                router,
                toast,
                tenant
            }
        },
        data() {
            return {
                types: this.tenantService.types(this.$t),
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
        mounted() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            this.tenantService.get(this.route.params.tenantId)
                .then(
                    (response) => {
                        this.tenant = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async updateTenant() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.tenantService.update(this.route.params.tenantId, this.tenant)
                        .then(
                            (response) => {
                                this.toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('tenants.updated'), life: 3000 });
                                this.saving = false;
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                    )
                }
            },
            
            back() {
                this.router.push({name: 'tenant_show', params: { tenantId : this.tenant.id }})
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2">{{ $t('tenants.basic_data') }}</h4>
        <form v-on:submit.prevent="updateTenant">
            <TenantForm :tenant="tenant" :types="types" :saving="saving" :loading="loading" :errors="errors" :v="v$" />
            
            <div v-if="loading">
                <ProgressSpinner style="width: 25px; height: 25px"/>
            </div>
            
            <div class="flex justify-content-between align-items-center">
                <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
            </div>
        </form>
    </div>
</template>