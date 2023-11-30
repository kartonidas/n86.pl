<script>
    import { ref, reactive, computed } from 'vue'
    import { useRoute } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import CustomerService from '@/service/CustomerService'
    import CustomerForm from './Form.vue'
    
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
            
            const route = useRoute()
            const customerService = new CustomerService()
            const toast = useToast();
            
            return {
                v$: useVuelidate(rules, state),
                customerService,
                route,
                toast,
                customer
            }
        },
        data() {
            return {
                types: this.customerService.types(),
                saving: false,
                loading: true,
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.customer_list'), route : { name : 'customers'} },
                        {'label' : this.$t('customers.edit_customer'), disabled : true },
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
            this.customerService.get(this.route.params.customerId)
                .then(
                    (response) => {
                        this.customer = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async updateCustomer() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.customerService.update(this.route.params.customerId, this.customer)
                        .then(
                            (response) => {
                                this.toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customers.updated'), life: 3000 });
                                this.saving = false;
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                    )
                }
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="card p-fluid mt-4">
        <form v-on:submit.prevent="updateCustomer">
            <CustomerForm :customer="customer" :types="types" :saving="saving" :errors="errors" :v="v$" />
            
            <div v-if="loading">
                <ProgressSpinner style="width: 25px; height: 25px"/>
            </div>
            
            <div class="text-right">
                <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
            </div>
        </form>
    </div>
</template>