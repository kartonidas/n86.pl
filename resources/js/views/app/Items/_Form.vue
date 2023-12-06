<script>
    import { ref } from 'vue'
    import { getValues, getResponseErrors, hasAccess } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf } from '@/utils/i18n-validators'
    
    import Countries from '@/data/countries.json'
    import CustomerForm from './../Customers/_Form.vue'
    import CustomerService from '@/service/CustomerService'
    import ItemService from '@/service/ItemService'
    
    export default {
        emits: ['submit-form', 'set-errors', 'back'],
        components: { CustomerForm },
        setup() {
            const customer = ref({
                type : 'person',
                contacts : {
                    email : [],
                    phone : []
                },
                country : 'PL'
            })
            
            const customerService = new CustomerService()
            const itemService = new ItemService()
            return {
                customer,
                customerService,
                hasAccess,
                itemService,
            }
        },
        data() {
            return {
                addCustomerModalVisible: false,
                countries: Countries[this.$i18n.locale],
                savingCustomerForm: false,
                settings: {
                    types: getValues('item_types'),
                    ownership_types: getValues('ownership_types'),
                },
                v: useVuelidate(),
                customerErrors: [],
            }
        },
        beforeMount() {
            this.getSettings()
        },
        props: {
            item: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            update: { type: Boolean },
            fromCustomer: { type: Boolean },
            source: { type: String, default: 'new' },
        },
        methods: {
            async getSettings() {
                this.itemService.settings()
                    .then(
                        (response) => {
                            this.settings.customers = response.data.customers
                        },
                        (response) => {
                            this.$emit('set-errors', getResponseErrors(response))
                        }
                    );
            },
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$router.push({name: 'item_show', params: { itemId : this.item.id }})
            },
            
            backFromRent() {
                this.$emit('back')
            },
            
            addCustomer() {
                this.savingCustomerForm = false
                this.addCustomerModalVisible = true
                this.customer = {
                    type : 'person',
                    contacts : {
                        email : [],
                        phone : []
                    },
                    country : 'PL'
                }
            },
            
            createCustomer() {
                this.savingCustomerForm = true
                this.customerService.create(this.customer)
                    .then(
                        (response) => {
                            this.getSettings()
                            this.item.customer_id = response.data
                            this.addCustomerModalVisible = false
                            
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('customers.added'), life: 3000 });
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.customerErrors = getResponseErrors(response)
                            this.savingCustomerForm = false
                        }
                    )
            }
        },
        validations () {
            return {
                item: {
                    name: { required },
                    type: { required },
                    ownership_type: { required },
                    street: { required },
                    city: { required },
                    zip: { required },
                    customer_id: { required: requiredIf(function() { return this.item.ownership_type == "manage" }) },
                }
            }
        },
    };
</script>

<template>
    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
        <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
    
        <div class="mb-4">
            <div class="p-fluid">
                <div class="formgrid grid">
                    <div class="field col-12 mb-4">
                        <label for="name" v-required class="block text-900 font-medium mb-2">{{ $t('items.name') }}</label>
                        <InputText id="name" type="text" :placeholder="$t('items.name')" class="w-full" :class="{'p-invalid' : v.item.name.$error}" v-model="item.name" :disabled="loading || saving"/>
                        <div v-if="v.item.name.$error">
                            <small class="p-error">{{ v.item.name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('items.estate_type') }}</label>
                        <Dropdown id="type" v-model="item.type" :options="settings.types" optionLabel="name" :class="{'p-invalid' : v.item.type.$error}" optionValue="id" :placeholder="$t('items.select_estate_type')" class="w-full" :disabled="loading || saving"/>
                        <div v-if="v.item.type.$error">
                            <small class="p-error">{{ v.item.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4" :class="[item.ownership_type == 'manage' ? 'md:col-4 ' : 'md:col-8']">
                        <label for="ownership_type" v-required class="block text-900 font-medium mb-2">{{ $t('items.ownership_type') }}</label>
                        <Dropdown id="ownership_type" v-model="item.ownership_type" :class="{'p-invalid' : v.item.ownership_type.$error}" :options="settings.ownership_types" optionLabel="name" optionValue="id" :placeholder="$t('items.select_ownership_type')" class="w-full" :disabled="loading || saving || fromCustomer"/>
                        <div v-if="v.item.ownership_type.$error">
                            <small class="p-error">{{ v.item.ownership_type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="item.ownership_type == 'manage'">
                        <label for="customer" v-required class="block text-900 font-medium mb-2">{{ $t('items.customer') }}</label>
                        <div class="flex">
                            <Dropdown v-model="item.customer_id" filter :options="settings.customers" :class="{'p-invalid' : v.item.customer_id.$error}" :style="[hasAccess('customer:create') ? 'width: calc(100% - 50px); margin-right: 10px;' : '']" optionLabel="name" optionValue="id" :placeholder="$t('items.select_customer')" class="w-full" :disabled="loading || saving || fromCustomer" />
                            <Button type="button" :loading="saving" v-tooltip.left="$t('items.add_customer')"  v-if="hasAccess('customer:create')" iconPos="right" @click="addCustomer" icon="pi pi-plus" class="p-button-secondary w-full text-center" style="min-width: 40px; max-width: 40px" :disabled="loading || saving || fromCustomer"></Button>
                        </div>
                        <div v-if="v.item.customer_id.$error">
                            <small class="p-error">{{ v.item.customer_id.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="formgrid grid ">
                    <div class="col-12">
                        <Divider align="left" class="mb-5">
                            {{ $t('items.address_data') }}
                        </Divider>
                    </div>
                </div>
                
                <div class="formgrid grid ">
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="street" v-required class="block text-900 font-medium mb-2">{{ $t('items.street') }}</label>
                        <InputText id="street" type="text" :placeholder="$t('items.street')" class="w-full" :class="{'p-invalid' : v.item.street.$error}" v-model="item.street" :disabled="loading || saving" />
                        <div v-if="v.item.street.$error">
                            <small class="p-error">{{ v.item.street.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('items.house_no') }}</label>
                        <InputText id="house_no" type="text" :placeholder="$t('items.house_no')" class="w-full" v-model="item.house_no" :disabled="loading || saving" />
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('items.apartment_no') }}</label>
                        <InputText id="apartment_no" type="text" :placeholder="$t('items.apartment_no')" class="w-full" v-model="item.apartment_no" :disabled="loading || saving" />
                    </div>
                    
                    <div class="field col-12 md:col-4 sm:col-12 mb-4">
                        <label for="country" class="block text-900 font-medium mb-2">{{ $t('items.country') }}</label>
                        <Dropdown id="country" v-model="item.country" filter :options="countries" optionLabel="name" optionValue="code" :placeholder="$t('items.select_country')" class="w-full" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-4 mb-4">
                        <label for="zip" v-required class="block text-900 font-medium mb-2">{{ $t('items.zip') }}</label>
                        <InputText id="zip" type="text" :placeholder="$t('items.zip')" class="w-full" :class="{'p-invalid' : v.item.zip.$error}" v-model="item.zip" :disabled="loading || saving" />
                        <div v-if="v.item.zip.$error">
                            <small class="p-error">{{ v.item.zip.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-5 sm:col-8 mb-4">
                        <label for="city" v-required class="block text-900 font-medium mb-2">{{ $t('items.city') }}</label>
                        <InputText id="city" type="text" :placeholder="$t('items.city')" class="w-full" :class="{'p-invalid' : v.item.city.$error}" v-model="item.city" :disabled="loading || saving" />
                        <div v-if="v.item.city.$error">
                            <small class="p-error">{{ v.item.city.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="formgrid grid ">
                    <div class="col-12">
                        <Divider align="left" class="mb-5">
                            {{ $t('items.additional_data') }}
                        </Divider>
                    </div>
                </div>
                
                <div class="formgrid grid ">
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="area" class="block text-900 font-medium mb-2">{{ $t('items.area') }} (m2)</label>
                        <InputNumber id="area" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.area')" class="w-full" v-model="item.area" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="number_of_rooms" class="block text-900 font-medium mb-2">{{ $t('items.number_of_rooms') }}</label>
                        <InputNumber id="number_of_rooms" :useGrouping="false" locale="pl-PL" :placeholder="$t('items.number_of_rooms')" class="w-full" v-model="item.num_rooms" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="default_rent_value" class="block text-900 font-medium mb-2">{{ $t('items.default_rent_value') }}</label>
                        <InputNumber id="default_rent_value" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.default_rent_value')" class="w-full" v-model="item.default_rent" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="default_deposit_value" class="block text-900 font-medium mb-2">{{ $t('items.default_deposit_value') }}</label>
                        <InputNumber id="default_deposit_value" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.default_deposit_value')" class="w-full" v-model="item.default_deposit" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="description" class="block text-900 font-medium mb-2">{{ $t('items.description') }}</label>
                        <Textarea id="description" type="text" :placeholder="$t('items.description')" rows="3" class="w-full" v-model="item.description" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="comments" class="block text-900 font-medium mb-2">{{ $t('items.comments') }}</label>
                        <Textarea id="comments" type="text" :placeholder="$t('items.comments')" rows="3" class="w-full" v-model="item.comments" :disabled="loading || saving"/>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <div v-if="source == 'new'">
                <div class="text-right">
                    <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
            <div v-else-if="source == 'rent' || source == 'rent:direct'">
                <div class="text-right" v-if="source == 'rent'">
                    <Button type="submit" :label="$t('app.next')" :loading="saving" iconPos="right" icon="pi pi-angle-right" class="w-auto text-center"></Button>
                </div>
                <div class="flex justify-content-between align-items-center" v-else>
                    <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="backFromRent" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.next')" :loading="saving" iconPos="right" icon="pi pi-angle-right" class="w-auto text-center"></Button>
                </div>
            </div>
            <div v-else>
                <div v-if="loading">
                    <ProgressSpinner style="width: 25px; height: 25px"/>
                </div>
                
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
        </div>
    </form>
    
    <Dialog v-model:visible="addCustomerModalVisible" modal :header="$t('items.add_customer')" style="{ width: '50rem' }" :breakpoints="{ '1499px': '90vw' }">
        <CustomerForm @submit-form="createCustomer" :customer="customer" :saving="savingCustomerForm" :errors="customerErrors" />
    </Dialog>
</template>