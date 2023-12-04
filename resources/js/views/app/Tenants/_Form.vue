<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, email } from '@/utils/i18n-validators'
    
    import Countries from '@/data/countries.json'
    import TenantService from '@/service/TenantService'
    import PhoneCodes from '@/data/phone_codes.json'
    
    export default {
        emits: ['submit-form'],
        setup() {
            const tenantService = new TenantService()
            return {
                tenantService,
            }
        },
        watch: {
            tenant() {
                this.toValidate = this.tenant
            }
        },
        data() {
            const toValidate = ref(this.tenant)
            const state = reactive({ 'tenant' : toValidate })
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
            
            return {
                phoneCodes : PhoneCodes,
                phoneCodesFilterFields : ['code', 'name'],
                countries: Countries[this.$i18n.locale],
                types: this.tenantService.types(this.$t),
                v: useVuelidate(rules, state),
                toValidate: toValidate
            }
        },
        props: {
            tenant: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            update: { type: Boolean },
        },
        computed: {
            labelTenantTypeName: {
                get: function () {
                    return this.tenant.type == 'person' ? this.$t('tenants.firstname_lastname') : this.$t('tenants.name')
                },
            }
        },
        methods: {
            addTenantPhone() {
                this.tenant.contacts.phone.push({ val : "", notification: 1, prefix : "+48" });
            },
            
            addTenantEmail() {
                this.tenant.contacts.email.push({ val : "", notification: 1 });
            },
            
            removeTenantPhone(index) {
                this.tenant.contacts.phone.splice(index, 1);
            },
            
            removeTenantEmail(index) {
                this.tenant.contacts.email.splice(index, 1);
            },
            
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$router.push({name: 'tenant_show', params: { tenantId : this.tenant.id }})
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
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('tenants.account_type') }}</label>
                        <Dropdown id="type" v-model="tenant.type" :options="types" optionLabel="name" optionValue="id" :placeholder="$t('tenants.account_type')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.tenant.type.$error">
                            <small class="p-error">{{ v.tenant.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="name" v-required class="block text-900 font-medium mb-2">{{ labelTenantTypeName }}</label>
                        <InputText id="name" type="text" :placeholder="$t('tenants.name')" class="w-full" :class="{'p-invalid' : v.tenant.name.$error}" v-model="tenant.name" :disabled="saving || loading"/>
                        <div v-if="v.tenant.name.$error">
                            <small class="p-error">{{ v.tenant.name.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="tenant.type == 'firm'">
                        <label for="nip" class="block text-900 font-medium mb-2">{{ $t('tenants.nip') }}</label>
                        <InputText id="nip" type="text" :placeholder="$t('tenants.nip')" class="w-full" v-model="tenant.nip" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="tenant.type == 'person'">
                        <label for="pesel" class="block text-900 font-medium mb-2">{{ $t('tenants.pesel') }}</label>
                        <InputText id="pesel" type="text" :placeholder="$t('tenants.pesel')" class="w-full" v-model="tenant.pesel" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="street" class="block text-900 font-medium mb-2">{{ $t('tenants.street') }}</label>
                        <InputText id="street" type="text" :placeholder="$t('tenants.street')" class="w-full" v-model="tenant.street" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('tenants.house_no') }}</label>
                        <InputText id="house_no" type="text" :placeholder="$t('tenants.house_no')" class="w-full" v-model="tenant.house_no" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-6 mb-4">
                        <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('tenants.apartment_no') }}</label>
                        <InputText id="apartment_no" type="text" :placeholder="$t('tenants.apartment_no')" class="w-full" v-model="tenant.apartment_no" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-4 sm:col-12 mb-4">
                        <label for="country" class="block text-900 font-medium mb-2">{{ $t('tenants.country') }}</label>
                        <Dropdown id="country" v-model="tenant.country" filter :options="countries" optionLabel="name" optionValue="code" :placeholder="$t('tenants.select_country')" class="w-full" :disabled="saving || loading"/>
                    </div>
                    
                    <div class="field col-12 md:col-3 sm:col-4 mb-4">
                        <label for="zip" class="block text-900 font-medium mb-2">{{ $t('tenants.zip') }}</label>
                        <InputText id="zip" type="text" :placeholder="$t('tenants.zip')" class="w-full" v-model="tenant.zip" :disabled="saving || loading" />
                    </div>
                    
                    <div class="field col-12 md:col-5 sm:col-8 mb-4">
                        <label for="city" class="block text-900 font-medium mb-2">{{ $t('tenants.city') }}</label>
                        <InputText id="city" type="text" :placeholder="$t('tenants.city')" class="w-full" v-model="tenant.city" :disabled="saving || loading"/>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="comments" class="block text-900 font-medium mb-2">{{ $t('tenants.comments') }}</label>
                        <Textarea id="comments" type="text" :placeholder="$t('tenants.comments')" rows="3" class="w-full" v-model="tenant.comments" :disabled="saving || loading"/>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label class="block text-900 font-medium mb-2">{{ $t('tenants.agree_notifications') }}:</label>
                        
                        <div class="formgrid grid mt-3">
                            <div class="col-6 sm:col-4 md:col-3 xl:col-2">
                                <div class="flex align-items-center">
                                    <div class="mr-3">{{ $t('tenants.sms') }}:</div>
                                    <InputSwitch v-model="tenant.send_sms" :trueValue="1" :disabled="saving || loading"/>
                                </div>
                            </div>
                            <div class="col-6 sm:col-4 md:col-3 xl:col-2">
                                <div class="flex align-items-center">
                                    <div class="mr-3">{{ $t('tenants.email2') }}:</div>
                                    <InputSwitch v-model="tenant.send_email" :trueValue="1" :disabled="saving || loading"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex justify-content-between align-items-center header-border-bottom pb-2 ">
                <h5 class="inline-flex mb-0 text-color">{{ $t('tenants.phone_list') }}</h5>
                <div class="text-right mb-0 inline-flex" >
                    <Button icon="pi pi-phone" @click="addTenantPhone" severity="secondary" v-tooltip.left="$t('tenants.add_phone')" :label="$t('tenants.add')" iconPos="right" class="text-center" size="small" :disabled="saving || loading"></Button>
                </div>
            </div>
            <div class="mb-5 mt-3">
                <div class="overflow-auto">
                    <table class="table w-full mt-2 mb-4" v-if="tenant.contacts.phone.length">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th style="min-width: 160px;"></th>
                                <th class="font-normal" style="width: 120px;">{{ $t('tenants.notifications') }}</th>
                            </tr>
                        </thead>
                        <tbody v-for="(phone, index) in tenant.contacts.phone">
                            <tr>
                                <td class="text-right">
                                    <Button type="button" @click="removeTenantPhone(index)" severity="danger" :disabled="saving || loading" iconPos="right" icon="pi pi-trash" class="text-center mr-2"></Button>
                                </td>
                                <td>
                                    <div class="flex">
                                        <div class="display-inline mr-3" style="min-width: 145px; max-width: 145px">
                                            <Dropdown id="type" :filterFields="phoneCodesFilterFields" filter v-model="tenant.contacts.phone[index].prefix" :options="phoneCodes" optionValue="code" :placeholder="$t('tenants.phone_code')" class="w-full" :disabled="saving || loading">
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
                                            <InputMask mask="999-999-999?999999" slotChar="" :class="{'p-invalid' : v.tenant.contacts.phone[index].$error}" :placeholder="$t('tenants.phone')" :value="phone.val" class="w-full" v-model="tenant.contacts.phone[index].val" :disabled="saving || loading"/>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <InputSwitch v-model="tenant.contacts.phone[index].notification" :trueValue="1"/>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div v-if="v.tenant.contacts.phone[index].$error">
                                        <small class="p-error">{{ v.tenant.contacts.phone[index].$errors[0].$message }}</small>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else>
                        <i>{{ $t("tenants.no_phones") }}</i>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-content-between align-items-center header-border-bottom pb-2 mb-5">
                <h5 class="inline-flex  mb-0 text-color">{{ $t('tenants.email_list') }}</h5>
                <div class="text-right mb-0 inline-flex" >
                    <Button icon="pi pi-at" @click="addTenantEmail" severity="secondary" v-tooltip.left="$t('tenants.add_email')" :label="$t('tenants.add')" iconPos="right" class="text-center" size="small" :disabled="saving || loading"></Button>
                </div>
            </div>
            <div class="mb-5 mt-3">
                <div class="overflow-auto">
                    <table class="table w-full mt-2 mb-4" v-if="tenant.contacts.email.length">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th style="min-width: 160px;"></th>
                                <th class="font-normal" style="width: 120px;">{{ $t('tenants.notifications') }}</th>
                            </tr>
                        </thead>
                        <tbody v-for="(email, index) in tenant.contacts.email">
                            <tr>
                                <td class="text-right">
                                    <Button type="button" @click="removeTenantEmail(index)" severity="danger" :disabled="saving || loading" iconPos="right" icon="pi pi-trash" class="text-center mr-2"></Button>
                                </td>
                                <td>
                                    <InputText type="text" :placeholder="$t('tenants.email')" :class="{'p-invalid' : v.tenant.contacts.email[index].$error}" :value="email.val" class="w-full" v-model="tenant.contacts.email[index].val" :disabled="saving || loading" />
                                </td>
                                <td class="text-center align-middle">
                                    <InputSwitch v-model="tenant.contacts.email[index].notification" :trueValue="1" />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div v-if="v.tenant.contacts.email[index].$error">
                                        <small class="p-error">{{ v.tenant.contacts.email[index].$errors[0].$message }}</small>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else>
                        <i>{{ $t("tenants.no_emails") }}</i>
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
