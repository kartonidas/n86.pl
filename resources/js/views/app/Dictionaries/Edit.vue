<script>
    import { ref } from 'vue'
    import { useRoute } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import DictionaryService from '@/service/DictionaryService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.dictionaries_edit')
            
            const route = useRoute()
            const dictionaryService = new DictionaryService()
            
            return {
                v$: useVuelidate(),
                dictionaryService,
                route,
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                dictionary: {},
                meta: {
                    breadcrumbItems: this.getBreadcrumbs(),
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            this.dictionaryService.get(this.route.params.dictionaryId)
                .then(
                    (response) => {
                        this.dictionary = response.data
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
            async updateDictionary() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.dictionaryService.update(this.route.params.dictionaryId, true, this.dictionary.name)
                        .then(
                            (response) => {
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('dictionaries.updated'), life: 3000 });
                                this.saving = false;
                            },
                            (errors) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(errors)
                                this.saving = false
                            }
                        )
                } else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            getBreadcrumbs() {
                let breadcrumbs = [
                    {'label' : this.$t('menu.settings'), disabled : true },
                    {'label' : this.$t('menu.dictionaries'), disabled : true },
                ]
                
                switch(this.route.params.type) {
                    case 'bills':
                        breadcrumbs.push({'label' : this.$t('menu.bill_type'), route : {name : 'dictionaries', params : {type : 'bills'}} });
                    break;
                    case 'fees':
                        breadcrumbs.push({'label' : this.$t('menu.fee_include_rent'), route : {name : 'dictionaries', params : {type : 'fees'}} });
                    break;
                    case 'payment_types':
                        breadcrumbs.push({'label' : this.$t('menu.payment_types'), route : {name : 'dictionaries', params : {type : 'payment_types'}} });
                    break;
                }
                
                breadcrumbs.push({'label' : this.$t('dictionaries.edit'), disabled: true });
                return breadcrumbs;
            }
        },
        validations () {
            return {
                dictionary: {
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
                <form v-on:submit.prevent="updateDictionary" class="sticky-footer-form">
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
                                    <label for="name" class="block text-900 font-medium mb-2" v-required>{{ $t('dictionaries.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('dictionaries.name')" class="w-full" :class="{'p-invalid' : v$.dictionary.name.$error}" v-model="dictionary.name" :disabled="loading || saving"/>
                                    <div v-if="v$.dictionary.name.$error">
                                        <small class="p-error">{{ v$.dictionary.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div class="" v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="text-right">
                            <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>