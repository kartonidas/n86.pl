<script>
    import { ref, reactive, computed } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import CustomerForm from './_Form.vue'
    import CustomerService from '@/service/CustomerService'
    
    export default {
        components: { CustomerForm },
        setup() {
            setMetaTitle('meta.title.customers_new')
            
            const customer = ref({
                type : 'person',
                contacts : {
                    email : [],
                    phone : []
                },
                country : 'PL'
            })
            const state = reactive({ 'customer' : customer})
            const rules = computed(() => {
                const rules = {
                    customer: {
                        name: { required },
                        type: { required },
                    }
                }
                
                rules.customer.contacts = {}
                if(state.customer.contacts.email.length)
                {
                    rules.customer.contacts.email = []
                    
                    for(var i = 0; i < state.customer.contacts.email.length; i++)
                        rules.customer.contacts.email.push({ val : { required, email } })
                }
                
                if(state.customer.contacts.phone.length)
                {
                    rules.customer.contacts.phone = []
                    
                    for(var i = 0; i < state.customer.contacts.phone.length; i++)
                        rules.customer.contacts.phone.push({ val : { required } })
                }
                
                return rules
            })
            
            const router = useRouter()
            const customerService = new CustomerService()
            
            return {
                v$: useVuelidate(rules, state),
                customerService,
                router,
                customer
            }
        },
        data() {
            return {
                types: this.customerService.types(this.$t),
                saving: false,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.customer_list'), route : { name : 'customers'} },
                        {'label' : this.$t('customers.new_customer'), disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createCustomer() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.customerService.create(this.customer)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.$t('app.success'),
                                    detail : this.$t('customers.added'),
                                });
                                
                                this.router.push({name: 'customer_show', params: { customerId : response.data }})
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
        <h4 class="mb-5 header-border-bottom pb-2">{{ $t('customers.basic_data') }}</h4>
        <form v-on:submit.prevent="createCustomer">
            <CustomerForm :customer="customer" :types="types" :saving="saving" :errors="errors" :v="v$" />
            
            <div class="text-right">
                <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
            </div>
        </form>
    </div>
</template>