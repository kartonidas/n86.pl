<script>
    import { ref } from 'vue'
    import { useRoute } from 'vue-router'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import CustomerForm from './_Form.vue'
    import CustomerService from '@/service/CustomerService'
    
    export default {
        components: { CustomerForm },
        setup() {
            setMetaTitle('meta.title.customers_edit')
            
            const customer = ref({
                type : 'person',
                contacts : {
                    email : [],
                    phone : []
                },
            })
            
            const route = useRoute()
            const customerService = new CustomerService()
            
            return {
                customerService,
                customer,
                route,
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
                        {'label' : this.$t('menu.customer_list'), route : { name : 'customers'} },
                        {'label' : this.$t('customers.card'), route : { name : 'customer_show'} },
                        {'label' : this.$t('customers.edit_customer'), disabled : true },
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
            this.customerService.get(this.route.params.customerId)
                .then(
                    (response) => {
                        this.customer = response.data
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
            async updateCustomer() {
                this.saving = true
                this.errors = []
                
                this.customerService.update(this.route.params.customerId, this.customer)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customers.updated'), life: 3000 });
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
    <div class="card p-fluid mt-4">
        <h4 class="mb-5 header-border-bottom pb-2 text-color font-medium">{{ $t('customers.basic_data') }}</h4>
        <CustomerForm @submit-form="updateCustomer" :customer="customer" :update="true" :saving="saving" :loading="loading" :errors="errors" />
    </div>
</template>