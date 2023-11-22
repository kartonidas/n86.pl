<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { getResponseErrors } from '@/utils/helper'
    import ProgressSpinner from 'primevue/progressspinner';
    import TabView from 'primevue/tabview';
    import TabPanel from 'primevue/tabpanel';
    
    import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
    import ItemService from '@/service/ItemService'
    import store from '@/store.js'
    
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
                    active: true,
                },
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.items'), route : { name : 'items'} },
                        {'label' : this.t('app.new_item'), route : { name : 'items'}, disabled : true },
                    ],
                }
            }
        },
        methods: {
            async createItem() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.itemService.create(this.item.active, this.item.name, this.item.street, this.item.house_no, this.item.apartment_no, this.item.city, this.item.zip)
                        .then(
                            (response) => {
                                store.commit('setToastMessage', {
                                    severity : 'success',
                                    summary : this.t('app.success'),
                                    detail : this.t('app.item_added'),
                                });
                                this.router.push({name: 'item_edit', params: { itemId : response.data }})
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
                    street: { required },
                    house_no: { required },
                    city: { required },
                    zip: { required },
                }
            }
        },
        components: {
            "Breadcrumb": AppBreadcrumb,
            "ProgressSpinner": ProgressSpinner,
            "TabView": TabView,
            "TabPanel": TabPanel,
        }
    }
</script>
    
<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <TabView>
                    <TabPanel header="Podstawowe informacje ">
                        <div class="mb-4">
                            <div class="p-fluid">
                                <div class="formgrid grid">
                                    <div class="field col-12 mb-2">
                                        <label for="name" class="block text-900 font-medium mb-2">{{ $t('app.name') }}</label>
                                        <InputText id="name" type="text" :placeholder="$t('app.name')" class="w-full" :class="{'p-invalid' : v$.item.name.$error}" v-model="item.name" :disabled="saving"/>
                                        <div v-if="v$.item.name.$error">
                                            <small class="p-error">{{ v$.item.name.$errors[0].$message }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="field col-12 mb-2">
                                        <div class="field-checkbox mb-0">
                                            <Checkbox inputId="activeCheck" name="active" value="1" v-model="item.active" :binary="true" :disabled="saving"/>
                                            <label for="activeCheck">{{ $t('app.active') }}</label>
                                        </div>
                                    </div>
                                    
                                    <div class="field col-12 md:col-6 mb-2">
                                        <label for="street" class="block text-900 font-medium mb-2">{{ $t('app.street') }}</label>
                                        <InputText id="street" type="text" :placeholder="$t('app.street')" class="w-full" :class="{'p-invalid' : v$.item.street.$error}" v-model="item.street" :disabled="saving" />
                                        <div v-if="v$.item.street.$error">
                                            <small class="p-error">{{ v$.item.street.$errors[0].$message }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="field col-12 md:col-3 sm:col-6 mb-2">
                                        <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('app.house_no') }}</label>
                                        <InputText id="house_no" type="text" :placeholder="$t('app.house_no')" class="w-full" :class="{'p-invalid' : v$.item.house_no.$error}" v-model="item.house_no" :disabled="saving" />
                                        <div v-if="v$.item.house_no.$error">
                                            <small class="p-error">{{ v$.item.house_no.$errors[0].$message }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="field col-12 md:col-3 sm:col-6 mb-2">
                                        <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('app.apartment_no') }}</label>
                                        <InputText id="apartment_no" type="text" :placeholder="$t('app.apartment_no')" class="w-full" v-model="item.apartment_no" :disabled="saving" />
                                    </div>
                                    
                                    <div class="field col-12 md:col-3 sm:col-5 mb-2">
                                        <label for="zip" class="block text-900 font-medium mb-2">{{ $t('app.zip') }}</label>
                                        <InputText id="zip" type="text" :placeholder="$t('app.zip')" class="w-full" :class="{'p-invalid' : v$.item.zip.$error}" v-model="item.zip" :disabled="saving" />
                                        <div v-if="v$.item.zip.$error">
                                            <small class="p-error">{{ v$.item.zip.$errors[0].$message }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="field col-12 md:col-9 sm:col-7 mb-2">
                                        <label for="city" class="block text-900 font-medium mb-2">{{ $t('app.city') }}</label>
                                        <InputText id="city" type="text" :placeholder="$t('app.city')" class="w-full" :class="{'p-invalid' : v$.item.city.$error}" v-model="item.city" :disabled="saving"/>
                                        <div v-if="v$.item.city.$error">
                                            <small class="p-error">{{ v$.item.city.$errors[0].$message }}</small>
                                        </div>
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
                        
                        <Button :label="$t('app.save')" :loading="saving" iconPos="right" @click="createItem" class="w-auto text-center"></Button>
                    </TabPanel>
                    <TabPanel header="Nejem" disabled="true"></TabPanel>
                    <TabPanel header="Rachunki" disabled="true"></TabPanel>
                </TabView>
            </div>
        </div>
    </div>
</template>