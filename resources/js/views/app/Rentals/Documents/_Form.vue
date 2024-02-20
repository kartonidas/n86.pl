<script>
    import { useVuelidate } from '@vuelidate/core'
    import { required, maxLength } from '@/utils/i18n-validators'
    import { getValues } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Editor from 'primevue/editor';
    import EditorToolbar from './../../_partials/EditorToolbar.vue'
    import RentalService from '@/service/RentalService'
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        components: { Editor, EditorToolbar },
        emits: ['submit-form', 'back', 'set-rental'],
        setup() {
            const rentalService = new RentalService()
            const documentTemplateService = new DocumentTemplateService();
            
            return {
                v: useVuelidate(),
                documentTemplateService,
                rentalService,
            }
        },
        data() {
            return {
                document_templates: {},
                rental: {
                    item: {},
                    tenant: {},
                },
                template_types: getValues('documents.types'),
                loading: true,
            }
        },
        props: {
            document: { type: Object },
            saving: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
        },
        beforeMount() {
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rental = response.data
                        
                        this.documentTemplateService.listGroupByType({size: 999, first: 0})
                            .then(
                                (response) => {
                                    this.document_templates = response.data;
                                    this.$emit('set-rental', this.rental)
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
                            this.document.title = response.data.title
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
            },
            async submitForm() {
                const result = await this.v.$validate()
                if (result) {
                    this.$emit('submit-form', this.document)
                }
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            documentTypeOnChange() {
                this.document.template = null
            },
            back() {
                this.$emit('back')
            }
        },
        validations () {
            return {
                document: {
                    type: { required },
                    content: { required },
                    title: { required, maxLengthValue: maxLength(200) },
                }
            }
        },
    }
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
                    <label for="template" class="block text-900 font-medium mb-2">{{ $t('rent.document_template') }}</label>
                    <Dropdown id="type" v-model="document.template" :options="templates()" optionLabel="name" optionValue="id" :placeholder="$t('rent.document_template')" class="w-full" :disabled="saving || loading"/>
                </div>
                
                <div class="field col-12 md:col-2 mb-4">
                    <label class="block text-900 font-medium mb-2">&nbsp;</label>
                    <Button type="button" @click="loadTemplate" :label="$t('rent.load')" :disabled="isSelected()" iconPos="right" icon="pi pi-cloud-download" class="mr-2"/>
                </div>
                
                
                <div class="field col-12 mb-4">
                    <label for="title" v-required class="block text-900 font-medium mb-2">{{ $t('rent.document_title') }}</label>
                    <InputText id="title" type="text" :placeholder="$t('rent.document_title')" class="w-full" :class="{'p-invalid' : v.document.title.$error}" v-model="document.title" :disabled="saving || loading"/>
                    <div v-if="v.document.title.$error">
                        <small class="p-error">{{ v.document.title.$errors[0].$message }}</small>
                    </div>
                </div>
                
                <div class="field col-12">
                    <label for="content" v-required class="block text-900 font-medium mb-2">{{ $t('rent.content') }}</label>
                    <Editor v-model="document.content" editorStyle="height: 500px">
                        <template v-slot:toolbar>
                            <EditorToolbar/>
                        </template>
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
</template>