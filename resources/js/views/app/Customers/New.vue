<script>
    import { ref } from 'vue'
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
            
            const customerService = new CustomerService()
            
            return {
                customerService,
                customer
            }
        },
        data() {
            return {
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
                            
                            this.$router.push({name: 'customer_show', params: { customerId : response.data }})
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
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2 text-color font-medium">{{ $t('customers.basic_data') }}</h4>
        <CustomerForm @submit-form="createCustomer" :customer="customer" :saving="saving" :errors="errors" />
    </div>
</template>