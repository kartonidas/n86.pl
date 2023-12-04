<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'
    
    import Countries from '@/data/countries.json'
    import CustomerService from '@/service/CustomerService'
    import PhoneCodes from '@/data/phone_codes.json'
    
    export default {
        emits: ['submit-form'],
        setup() {
            const customerService = new CustomerService()
            return {
                customerService,
            }
        },
        watch: {
            customer() {
                this.toValidate = this.customer
            }
        },
        data() {
            const toValidate = ref(this.customer)
            const state = reactive({ 'customer' : toValidate })
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
            
            return {
                phoneCodes : PhoneCodes,
                phoneCodesFilterFields : ['code', 'name'],
                countries: Countries[this.$i18n.locale],
                types: this.customerService.types(this.$t),
                v: useVuelidate(rules, state),
                toValidate: toValidate
            }
        },
        props: {
            customer: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            update: { type: Boolean },
        },
        computed: {
            labelCustomerTypeName: {
                get: function () {
                    return this.customer.type == 'person' ? this.$t('customers.firstname_lastname') : this.$t('customers.name')
                },
            }
        },
        methods: {
            addCustomerPhone() {
                this.customer.contacts.phone.push({ val : "", notification: 1, prefix : "+48" });
            },
            
            addCustomerEmail() {
                this.customer.contacts.email.push({ val : "", notification: 1 });
            },
            
            removeCustomerPhone(index) {
                this.customer.contacts.phone.splice(index, 1);
            },
            
            removeCustomerEmail(index) {
                this.customer.contacts.email.splice(index, 1);
            },
            
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$router.push({name: 'customer_show', params: { customerId : this.customer.id }})
            }
        }
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
                    <div class="field col-12  md:col-4 mb-4">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('customers.account_type') }}</label>
                        <Dropdown id="type" v-model="customer.type" :options="types" optionLabel="name" optionValue="id" :placeholder="$t('customers.account_type')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.customer.type.$error">
                            <small class="p-error">{{ v.customer.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="name" v-required class="block text-900 font-medium mb-2">{{ labelCustomerTypeName }}</label>
                        <InputText id="name" type="text" :placeholder="$t('customers.name')" class="w-full" :class="{'p-invalid' : v.customer.name.$error}" v-model="customer.name" :disabled="saving || loading"/>
                        <div v-if="v.customer.name.$error">
                            <small class="p-error">{{ v.customer.name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="customer.type == 'firm'">
                        <label for="nip" class="block text-900 font-medium mb-2">{{ $t('customers.nip') }}</label>
                        <InputText id="nip" type="text" :placeholder="$t('customers.nip')" class="w-full" v-model="customer.nip" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="customer.type == 'person'">
                        <label for="pesel" class="block text-900 font-medium mb-2">{{ $t('customers.pesel') }}</label>
                        <InputText id="pesel" type="text" :placeholder="$t('customers.pesel')" class="w-full" v-model="customer.pesel" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="street" class="block text-900 font-medium mb-2">{{ $t('customers.street') }}</label>
                        <InputText id="street" type="text" :placeholder="$t('customers.street')" class="w-full" v-model="customer.street" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('customers.house_no') }}</label>
                        <InputText id="house_no" type="text" :placeholder="$t('customers.house_no')" class="w-full" v-model="customer.house_no" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('customers.apartment_no') }}</label>
                        <InputText id="apartment_no" type="text" :placeholder="$t('customers.apartment_no')" class="w-full" v-model="customer.apartment_no" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-4 sm:col-12 mb-4">
                        <label for="country" class="block text-900 font-medium mb-2">{{ $t('customers.country') }}</label>
                        <Dropdown id="country" v-model="customer.country" filter :options="countries" optionLabel="name" optionValue="code" :placeholder="$t('customers.select_country')" class="w-full" :disabled="saving || loading"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-4 mb-4">
                        <label for="zip" class="block text-900 font-medium mb-2">{{ $t('customers.zip') }}</label>
                        <InputText id="zip" type="text" :placeholder="$t('customers.zip')" class="w-full" v-model="customer.zip" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-5 sm:col-8 mb-4">
                        <label for="city" class="block text-900 font-medium mb-2">{{ $t('customers.city') }}</label>
                        <InputText id="city" type="text" :placeholder="$t('customers.city')" class="w-full" v-model="customer.city" :disabled="saving || loading"/>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="comments" class="block text-900 font-medium mb-2">{{ $t('customers.comments') }}</label>
                        <Textarea id="comments" type="text" :placeholder="$t('customers.comments')" rows="3" class="w-full" v-model="customer.comments" :disabled="saving || loading"/>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label class="block text-900 font-medium mb-2">{{ $t('customers.agree_notifications') }}:</label>
                        
                        <div class="formgrid grid mt-3">
                            <div class="col-6 sm:col-4 md:col-3 xl:col-2">
                                <div class="flex align-items-center">
                                    <div class="mr-3">{{ $t('customers.sms') }}:</div>
                                    <InputSwitch v-model="customer.send_sms" :trueValue="1" :disabled="saving || loading"/>
                                </div>
                            </div>
                            <div class="col-6 sm:col-4 md:col-3 xl:col-2">
                                <div class="flex align-items-center">
                                    <div class="mr-3">{{ $t('customers.email2') }}:</div>
                                    <InputSwitch v-model="customer.send_email" :trueValue="1" :disabled="saving || loading"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex justify-content-between align-items-center header-border-bottom pb-2 ">
                <h5 class="inline-flex mb-0 text-color font-medium">{{ $t('customers.phone_list') }}</h5>
                <div class="text-right mb-0 inline-flex" >
                    <Button icon="pi pi-phone" @click="addCustomerPhone" severity="secondary" v-tooltip.left="$t('customers.add_phone')" :label="$t('customers.add')" iconPos="right" class="text-center" size="small" :disabled="saving || loading"></Button>
                </div>
            </div>
            <div class="mb-5 mt-3">
                <div class="overflow-auto">
                    <table class="table w-full mt-2 mb-4" v-if="customer.contacts.phone.length">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th style="min-width: 160px;"></th>
                                <th class="font-normal" style="width: 120px;">{{ $t('customers.notifications') }}</th>
                            </tr>
                        </thead>
                        <tbody v-for="(phone, index) in customer.contacts.phone">
                            <tr>
                                <td class="text-right">
                                    <Button type="button" @click="removeCustomerPhone(index)" severity="danger" :disabled="saving || loading" iconPos="right" icon="pi pi-trash" class="text-center mr-2"></Button>
                                </td>
                                <td>
                                    <div class="flex">
                                        <div class="display-inline mr-3" style="min-width: 145px; max-width: 145px">
                                            <Dropdown id="type" :filterFields="phoneCodesFilterFields" filter v-model="customer.contacts.phone[index].prefix" :options="phoneCodes" optionValue="code" :placeholder="$t('customers.phone_code')" class="w-full" :disabled="saving || loading">
                                                <template #value="slotProps">
                                                    <span v-if="slotProps.value">
                                                        {{ slotProps.value }}
                                                    </span>
                                                    <span v-else>
                                                        {{ slotProps.placeholder }}
                                                    </span>
                                                </template>
                                                <template #option="slotProps">
                                                    ({{ slotProps.option.code }}) {{ slotProps.option.name }}
                                                </template>
                                            </Dropdown>
                                        </div>
                                        <div class="w-full" style="min-width: 150px">
                                            <InputMask mask="999-999-999?999999" slotChar="" :class="{'p-invalid' : v.customer.contacts.phone[index].$error}" :placeholder="$t('customers.phone')" :value="phone.val" class="w-full" v-model="customer.contacts.phone[index].val" :disabled="saving || loading"/>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <InputSwitch v-model="customer.contacts.phone[index].notification" :trueValue="1"/>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div v-if="v.customer.contacts.phone[index].$error">
                                        <small class="p-error">{{ v.customer.contacts.phone[index].$errors[0].$message }}</small>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else>
                        <i>{{ $t("customers.no_phones") }}</i>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-content-between align-items-center header-border-bottom pb-2 mb-5">
                <h5 class="inline-flex  mb-0 text-color font-medium">{{ $t('customers.email_list') }}l</h5>
                <div class="text-right mb-0 inline-flex" >
                    <Button icon="pi pi-at" @click="addCustomerEmail" severity="secondary" v-tooltip.left="$t('customers.add_email')" :label="$t('customers.add')" iconPos="right" class="text-center" size="small" :disabled="saving || loading"></Button>
                </div>
            </div>
            <div class="mb-5 mt-3">
                <div class="overflow-auto">
                    <table class="table w-full mt-2 mb-4" v-if="customer.contacts.email.length">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th style="min-width: 160px;"></th>
                                <th class="font-normal" style="width: 120px;">{{ $t('customers.notifications') }}</th>
                            </tr>
                        </thead>
                        <tbody v-for="(email, index) in customer.contacts.email">
                            <tr>
                                <td class="text-right">
                                    <Button type="button" @click="removeCustomerEmail(index)" severity="danger" :disabled="saving || loading" iconPos="right" icon="pi pi-trash" class="text-center mr-2"></Button>
                                </td>
                                <td>
                                    <InputText type="text" :placeholder="$t('customers.email')" :class="{'p-invalid' : v.customer.contacts.email[index].$error}" :value="email.val" class="w-full" v-model="customer.contacts.email[index].val" :disabled="saving || loading" />
                                </td>
                                <td class="text-center align-middle">
                                    <InputSwitch v-model="customer.contacts.email[index].notification" :trueValue="1" />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div v-if="v.customer.contacts.email[index].$error">
                                        <small class="p-error">{{ v.customer.contacts.email[index].$errors[0].$message }}</small>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else>
                        <i>{{ $t("customers.no_emails") }}</i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <div v-if="!update">
                <div class="text-right">
                    <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
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
</template>
