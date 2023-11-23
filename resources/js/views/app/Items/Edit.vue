<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRoute } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { getResponseErrors } from '@/utils/helper'
    import store from '@/store.js'
    
    import ItemService from '@/service/ItemService'
    
    export default {
        setup() {
            const route = useRoute()
            const itemService = new ItemService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                t,
                v$: useVuelidate(),
                itemService,
                route,
                toast
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                item: {},
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.items'), disabled : true },
                        {'label' : this.t('app.estate_list'), route : { name : 'items'} },
                        {'label' : this.t('app.edit_item'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            if(store.getters.toastMessage) {
                let m = store.getters.toastMessage
                this.toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                store.commit('setToastMessage', null);
            }
            this.itemService.get(this.route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            async updateItem() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.itemService.update(this.route.params.itemId, this.item.active, this.item.name, this.item.street, this.item.house_no, this.item.apartment_no, this.item.city, this.item.zip)
                        .then(
                            (response) => {
                                this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('app.item_updated'), life: 3000 });
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
                item: {
                    name: { required },
                    street: { required },
                    house_no: { required },
                    city: { required },
                    zip: { required },
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
                <form v-on:submit.prevent="updateItem">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 mb-2">
                                    <label for="name" class="block text-900 font-medium mb-2">{{ $t('app.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('app.name')" class="w-full" :class="{'p-invalid' : v$.item.name.$error}" v-model="item.name" :disabled="loading || saving"/>
                                    <div v-if="v$.item.name.$error">
                                        <small class="p-error">{{ v$.item.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-2">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="activeCheck" name="active" value="1" v-model="item.active" :binary="true" :disabled="loading || saving"/>
                                        <label for="activeCheck">{{ $t('app.active') }}</label>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-2">
                                    <label for="street" class="block text-900 font-medium mb-2">{{ $t('app.street') }}</label>
                                    <InputText id="street" type="text" :placeholder="$t('app.street')" class="w-full" :class="{'p-invalid' : v$.item.street.$error}" v-model="item.street" :disabled="loading || saving" />
                                    <div v-if="v$.item.street.$error">
                                        <small class="p-error">{{ v$.item.street.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-2">
                                    <label for="house_no" class="block text-900 font-medium mb-2">{{ $t('app.house_no') }}</label>
                                    <InputText id="house_no" type="text" :placeholder="$t('app.house_no')" class="w-full" :class="{'p-invalid' : v$.item.house_no.$error}" v-model="item.house_no" :disabled="loading || saving" />
                                    <div v-if="v$.item.house_no.$error">
                                        <small class="p-error">{{ v$.item.house_no.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-6 mb-2">
                                    <label for="apartment_no" class="block text-900 font-medium mb-2">{{ $t('app.apartment_no') }}</label>
                                    <InputText id="apartment_no" type="text" :placeholder="$t('app.apartment_no')" class="w-full" v-model="item.apartment_no" :disabled="loading || saving" />
                                </div>
                                
                                <div class="field col-12 md:col-3 sm:col-5 mb-2">
                                    <label for="zip" class="block text-900 font-medium mb-2">{{ $t('app.zip') }}</label>
                                    <InputText id="zip" type="text" :placeholder="$t('app.zip')" class="w-full" :class="{'p-invalid' : v$.item.zip.$error}" v-model="item.zip" :disabled="loading || saving" />
                                    <div v-if="v$.item.zip.$error">
                                        <small class="p-error">{{ v$.item.zip.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-9 sm:col-7 mb-2">
                                    <label for="city" class="block text-900 font-medium mb-2">{{ $t('app.city') }}</label>
                                    <InputText id="city" type="text" :placeholder="$t('app.city')" class="w-full" :class="{'p-invalid' : v$.item.city.$error}" v-model="item.city" :disabled="loading || saving" />
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
                    
                    <div v-if="loading">
                        <ProgressSpinner style="width: 25px; height: 25px"/>
                    </div>
                    
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" class="w-auto text-center"></Button>
                </form>
            </div>
        </div>
    </div>
</template>