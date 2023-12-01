<script>
    import Countries from '@/data/countries.json'
    
    export default {
        data() {
            return {
                countries: Countries[this.$i18n.locale],
            }
        },
        props: {
            item: { type: Object },
            settings: {type: Object},
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            v: { type: Object }
        },
    };
</script>

<template>
    <div class="mb-4">
        <div class="p-fluid">
            <div class="formgrid grid">
                <div class="field col-12 md:col-6 mb-4">
                    <label for="name" v-required class="block text-900 font-medium mb-2">{{ $t('items.name') }}</label>
                    <InputText id="name" type="text" :placeholder="$t('items.name')" class="w-full" :class="{'p-invalid' : v.item.name.$error}" v-model="item.name" :disabled="loading || saving"/>
                    <div v-if="v.item.name.$error">
                        <small class="p-error">{{ v.item.name.$errors[0].$message }}</small>
                    </div>
                </div>
                
                <div class="field col-12 md:col-6 mb-4">
                    <label for="type" v-required class="block text-900 font-medium mb-2" sddsds>{{ $t('items.estate_type') }}</label>
                    <Dropdown id="type" v-model="item.type" :options="settings.types" optionLabel="name" optionValue="id" :placeholder="$t('items.select_estate_type')" class="w-full" :disabled="loading || saving"/>
                    <div v-if="v.item.type.$error">
                        <small class="p-error">{{ v.item.type.$errors[0].$message }}</small>
                    </div>
                </div>
                
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
                
                <div class="field col-12 mb-4" v-if="settings.showCustomers">
                    <div class="field-checkbox mb-0">
                        <Checkbox inputId="ownershipCheck" name="ownership" value="1" :falseValue="1" :trueValue="0" v-model="item.ownership" :binary="true" :disabled="loading || saving"/>
                        <label for="ownershipCheck">{{ $t('items.i_managed') }}</label>
                    </div>
                </div>
                
                <div class="field col-12 mb-4" v-if="settings.showCustomers && !item.ownership">
                    <label for="customer" v-required class="block text-900 font-medium mb-2">{{ $t('items.customer') }}</label>
                    <Dropdown v-model="item.customer_id" filter :options="settings.customers" optionLabel="name" optionValue="id" :placeholder="$t('items.select_customer')" class="w-full" :disabled="loading || saving" />
                    <div v-if="v.item.customer_id.$error">
                        <small class="p-error">{{ v.item.customer_id.$errors[0].$message }}</small>
                    </div>
                </div>
                
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
    <Message severity="error" :closable="false" v-if="errors.length">
        <ul class="list-unstyled">
            <li v-for="error of errors">
                {{ error }}
            </li>
        </ul>
    </Message>
</template>