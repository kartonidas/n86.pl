<script>
    import { setMetaTitle, getValues } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { appStore } from '@/store.js'
    
    import Editor from 'primevue/editor';
    import RentalService from '@/service/RentalService'
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        components: { Editor },
        setup() {
            setMetaTitle('meta.title.rent_new_document')
            
            const rentalService = new RentalService()
            const documentTemplateService = new DocumentTemplateService();
            
            return {
                documentTemplateService,
                rentalService,
                v: useVuelidate(),
            }
        },
        data() {
            return {
                document: {},
                document_templates: {},
                errors: [],
                rental: {
                    item: {},
                    tenant: {},
                },
                loading: true,
                saving: false,
                template_types: getValues('documents.types'),
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                        
                    ],
                }
            }
        },
        beforeMount() {
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rental = response.data
                        
                        this.documentTemplateService.listGroupByType(999, 1)
                            .then(
                                (response) => {
                                    this.document_templates = response.data;
                                    this.loading = false
                                },
                                (errors) => {
                                    this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                                }
                            )
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
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                ]
                
                if(this.rental.full_number != undefined)
                {
                    items.push({'label' : this.rental.full_number, route : { name : 'rental_show'} })
                    items.push({'label' : this.$t('rent.new_document'), disabled : true })
                }
                    
                return items
            },
            templates() {
                if(this.document.type != undefined)
                {
                    if (this.document_templates[this.document.type] != undefined)
                        return this.document_templates[this.document.type];
                }
                return [];
            },
            isSelected() {
                if (this.document.type && this.document.template && this.document_templates[this.document.type] != undefined) 
                    return false;
                return true;
            },
            loadTemplate() {
                this.rentalService.generateTemplateDocument(this.$route.params.rentalId, this.document.type, this.document.template).
                    then(
                        (response) => {
                            this.document.content = response.data.content
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    alert('submit')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            documentTypeOnChange() {
                this.document.template = null
            },
            back() {
                this.$router.push({name: 'rental_show'})
            }
        },
        validations () {
            return {
                document: {
                    type: { required },
                    content: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
                    <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    <div class="p-fluid">
                        <div class="formgrid grid">
                            <div class="field col-12 md:col-5 mb-4">
                                <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('rent.document_type') }}</label>
                                <Dropdown id="type" @change="documentTypeOnChange" v-model="document.type" :options="template_types" :class="{'p-invalid' : v.document.type.$error}" optionLabel="name" optionValue="id" :placeholder="$t('rent.document_type')" class="w-full" :disabled="saving || loading"/>
                                <div v-if="v.document.type.$error">
                                    <small class="p-error">{{ v.document.type.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-5 mb-4">
                                <label for="template" v-required class="block text-900 font-medium mb-2">{{ $t('rent.document_template') }}</label>
                                <Dropdown id="type" v-model="document.template" :options="templates()" optionLabel="name" optionValue="id" :placeholder="$t('rent.document_template')" class="w-full" :disabled="saving || loading"/>
                            </div>
                            
                            <div class="field col-12 md:col-2 mb-4">
                                <label class="block text-900 font-medium mb-2">&nbsp;</label>
                                <Button type="button" @click="loadTemplate" :label="$t('rent.load')" :disabled="isSelected()" iconPos="right" icon="pi pi-cloud-download" class="mr-2"/>
                            </div>
                            
                            <div class="field col-12">
                                <label for="content" v-required class="block text-900 font-medium mb-2">{{ $t('rent.content') }}</label>
                                <Editor v-model="document.content" editorStyle="height: 320px">
                           
                                </Editor>
                                <div v-if="v.document.content.$error">
                                    <small class="p-error">{{ v.document.content.$errors[0].$message }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="flex justify-content-between align-items-center">
                            <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                            <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>