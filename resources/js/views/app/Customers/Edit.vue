<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRoute } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import CustomerService from '@/service/CustomerService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.customers_edit')
            
            const route = useRoute()
            const customerService = new CustomerService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                t,
                v$: useVuelidate(),
                customerService,
                route,
                toast
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                customer: {},
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('menu.estates'), disabled : true },
                        {'label' : this.t('menu.customer_list'), route : { name : 'customers'} },
                        {'label' : this.t('customers.edit_customer'), disabled : true },
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
                        this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async updateCustomer() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.customerService.update(this.route.params.customerId, this.customer.name, this.customer.street, this.customer.house_no, this.customer.apartment_no, this.customer.city, this.customer.zip, this.customer.nip)
                        .then(
                            (response) => {
                                this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('customers.updated'), life: 3000 });
                                this.saving = false;
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                    )
                }
            }
        },
        validations () {
            return {
                customer: {
                    name: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <form v-on:submit.prevent="updateCustomer">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="name" v-required class="block text-900 font-medium mb-2">{{ $t('customers.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('customers.name')" class="w-full" :class="{'p-invalid' : v$.customer.name.$error}" v-model="customer.name" :disabled="loading || saving"/>
                                    <div v-if="v$.customer.name.$error">
                                        <small class="p-error">{{ v$.customer.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="nip" class="block text-900 font-medium mb-2">{{ $t('customers.nip') }}</label>
                                    <InputText id="nip" type="text" :placeholder="$t('customers.nip')" class="w-full" v-model="customer.nip" :disabled="loading || saving" />
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="street" class="block text-900 font-medium mb-2">{{ $t('customers.street') }}</label>
                                    <InputText id="street" type="text" :placeholder="$t('customers.street')" class="w-full" v-model="customer.street" :disabled="loading || saving" />
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('customers.house_no') }}</label>
                                    <InputText id="house_no" type="text" :placeholder="$t('customers.house_no')" class="w-full" v-model="customer.house_no" :disabled="loading || saving" />
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('customers.apartment_no') }}</label>
                                    <InputText id="apartment_no" type="text" :placeholder="$t('customers.apartment_no')" class="w-full" v-model="customer.apartment_no" :disabled="loading || saving" />
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-5 mb-4">
                                    <label for="zip" class="block text-900 font-medium mb-2">{{ $t('customers.zip') }}</label>
                                    <InputText id="zip" type="text" :placeholder="$t('customers.zip')" class="w-full" v-model="customer.zip" :disabled="loading || saving" />
                                </div>
                                
                                <div class="field col-12 md:col-9 sm:col-7 mb-4">
                                    <label for="city" class="block text-900 font-medium mb-2">{{ $t('customers.city') }}</label>
                                    <InputText id="city" type="text" :placeholder="$t('customers.city')" class="w-full" v-model="customer.city" :disabled="loading || saving" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <Message severity="error" :closable="false" v-if="errors.length">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    
                    <div v-if="loading">
                        <ProgressSpinner style="width: 25px; height: 25px"/>
                    </div>
                    
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>