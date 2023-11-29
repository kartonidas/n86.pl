<script>
    import { ref } from 'vue'
    import { useRouter, useRoute } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { useI18n } from 'vue-i18n'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'

    import DictionaryService from '@/service/DictionaryService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.dictionaries_new')
            
            const router = useRouter()
            const route = useRoute()
            const dictionaryService = new DictionaryService()
            const { t } = useI18n();
            
            return {
                router,
                route,
                t,
                v$: useVuelidate(),
                dictionaryService,
            }
        },
        data() {
            return {
                saving: false,
                dictionary: {
                    active : true
                },
                errors: [],
                type: this.route.params.type,
                meta: {
                    breadcrumbItems: this.getBreadcrumbs(),
                }
            }
        },
        methods: {
            async createDictionary() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.dictionaryService.create(this.type, this.dictionary.active, this.dictionary.name)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.t('app.success'),
                                    detail : this.t('dictionaries.added'),
                                });
                                
                                if(hasAccess('dictionary:update'))
                                    this.router.push({name: 'dictionary_edit', params: { type : this.type, dictionaryId : response.data }})
                                else
                                    this.router.push({name: 'dictionaries', params: { type : this.type } })
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        )
                }
            },
            getBreadcrumbs() {
                let breadcrumbs = [
                    {'label' : this.t('menu.settings'), disabled : true },
                    {'label' : this.t('menu.dictionaries'), disabled : true },
                ]
                
                switch(this.route.params.type) {
                    case 'bills':
                        breadcrumbs.push({'label' : this.t('menu.bill_type'), route : {name : 'dictionaries', params : {type : 'bills'}} });
                    break;
                    case 'fees':
                        breadcrumbs.push({'label' : this.t('menu.fee_include_rent'), route : {name : 'dictionaries', params : {type : 'fees'}} });
                    break;
                }
                
                breadcrumbs.push({'label' : this.t('dictionaries.add'), disabled: true });
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
                <form v-on:submit.prevent="createDictionary">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 mb-4">
                                    <label for="name" class="block text-900 font-medium mb-2">{{ $t('dictionaries.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('dictionaries.name')" class="w-full" :class="{'p-invalid' : v$.dictionary.name.$error}" v-model="dictionary.name" :disabled="saving"/>
                                    <div v-if="v$.dictionary.name.$error">
                                        <small class="p-error">{{ v$.dictionary.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="activeCheck" name="active" value="1" v-model="dictionary.active" :binary="true" :disabled="saving"/>
                                        <label for="activeCheck">{{ $t('dictionaries.active') }}</label>
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
                    
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>