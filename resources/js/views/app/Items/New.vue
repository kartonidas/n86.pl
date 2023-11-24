<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess } from '@/utils/helper'
    
    import { appStore } from '@/store.js'
    import ItemService from '@/service/ItemService'
    
    export default {
        setup() {
            const router = useRouter()
            const itemService = new ItemService()
            const { t } = useI18n();
            
            return {
                t,
                v$: useVuelidate(),
                itemService,
                router
            }
        },
        data() {
            return {
                saving: false,
                item : {
                    ownership: true,
                },
                settings: {
                    showCustomers: false,
                },
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('menu.estates'), disabled : true },
                        {'label' : this.t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.t('items.new_item'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            this.itemService.settings()
                .then(
                    (response) => {
                        this.settings.types = response.data.types
                        this.item.type = response.data.default_type
                        this.settings.showCustomers = response.data.customer_access
                        this.settings.customers = this.settings.showCustomers ? response.data.customers : []
                    },
                    (response) => {
                        this.errors = getResponseErrors(response)
                    }
                );
        },
        methods: {
            async createItem() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.itemService.create(this.item)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.t('app.success'),
                                    detail : this.t('items.added'),
                                });
                                
                                this.router.push({name: 'item_show', params: { itemId : response.data }})
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        )
                }
            },
        },
        validations () {
            return {
                item: {
                    name: { required },
                    type: { required },
                    street: { required },
                    city: { required },
                    zip: { required },
                    customer_id: { required: requiredIf(function() { return !this.item.ownership }) },
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
                <form v-on:submit.prevent="createItem">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="name" v-required class="block text-900 font-medium mb-2" sddsds>{{ $t('items.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('items.name')" class="w-full" :class="{'p-invalid' : v$.item.name.$error}" v-model="item.name" :disabled="saving"/>
                                    <div v-if="v$.item.name.$error">
                                        <small class="p-error">{{ v$.item.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="type" v-required class="block text-900 font-medium mb-2" sddsds>{{ $t('items.estate_type') }}</label>
                                    <Dropdown id="type" v-model="item.type" :options="settings.types" optionLabel="name" optionValue="id" :placeholder="$t('items.select_estate_type')" class="w-full" :disabled="saving" />
                                    <div v-if="v$.item.type.$error">
                                        <small class="p-error">{{ v$.item.type.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="street" v-required class="block text-900 font-medium mb-2">{{ $t('items.street') }}</label>
                                    <InputText id="street" type="text" :placeholder="$t('items.street')" class="w-full" :class="{'p-invalid' : v$.item.street.$error}" v-model="item.street" :disabled="saving" />
                                    <div v-if="v$.item.street.$error">
                                        <small class="p-error">{{ v$.item.street.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('items.house_no') }}</label>
                                    <InputText id="house_no" type="text" :placeholder="$t('items.house_no')" class="w-full" v-model="item.house_no" :disabled="saving" />
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('items.apartment_no') }}</label>
                                    <InputText id="apartment_no" type="text" :placeholder="$t('items.apartment_no')" class="w-full" v-model="item.apartment_no" :disabled="saving" />
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-5 mb-4">
                                    <label for="zip" v-required class="block text-900 font-medium mb-2">{{ $t('items.zip') }}</label>
                                    <InputText id="zip" type="text" :placeholder="$t('items.zip')" class="w-full" :class="{'p-invalid' : v$.item.zip.$error}" v-model="item.zip" :disabled="saving" />
                                    <div v-if="v$.item.zip.$error">
                                        <small class="p-error">{{ v$.item.zip.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-9 sm:col-7 mb-4">
                                    <label for="city" v-required class="block text-900 font-medium mb-2">{{ $t('items.city') }}</label>
                                    <InputText id="city" type="text" :placeholder="$t('items.city')" class="w-full" :class="{'p-invalid' : v$.item.city.$error}" v-model="item.city" :disabled="saving"/>
                                    <div v-if="v$.item.city.$error">
                                        <small class="p-error">{{ v$.item.city.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4" v-if="settings.showCustomers">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="ownershipCheck" name="ownership" value="1" :falseValue="1" :trueValue="0" v-model="item.ownership" :binary="true" :disabled="saving"/>
                                        <label for="ownershipCheck">{{ $t('items.i_managed') }}</label>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4" v-if="settings.showCustomers && !item.ownership">
                                    <label for="customer" v-required class="block text-900 font-medium mb-2">{{ $t('items.customer') }}</label>
                                    <Dropdown v-model="item.customer_id" filter :options="settings.customers" optionLabel="name" optionValue="id" :placeholder="$t('items.select_customer')" class="w-full" :disabled="saving"/>
                                    <div v-if="v$.item.customer_id.$error">
                                        <small class="p-error">{{ v$.item.customer_id.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="area" class="block text-900 font-medium mb-2">{{ $t('items.area') }} (m2)</label>
                                    <InputNumber id="area" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.area')" class="w-full" v-model="item.area" :disabled="saving"/>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="number_of_rooms" class="block text-900 font-medium mb-2">{{ $t('items.number_of_rooms') }}</label>
                                    <InputNumber id="number_of_rooms" :useGrouping="false" locale="pl-PL" :placeholder="$t('items.number_of_rooms')" class="w-full" v-model="item.num_rooms" :disabled="saving"/>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="default_rent_value" class="block text-900 font-medium mb-2">{{ $t('items.default_rent_value') }}</label>
                                    <InputNumber id="default_rent_value" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.default_rent_value')" class="w-full" v-model="item.default_rent" :disabled="saving"/>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-4">
                                    <label for="default_deposit_value" class="block text-900 font-medium mb-2">{{ $t('items.default_deposit_value') }}</label>
                                    <InputNumber id="default_deposit_value" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.default_deposit_value')" class="w-full" v-model="item.default_deposit" :disabled="saving"/>
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
                    
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>