<script>
    import { setMetaTitle, getResponseErrors } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import RentalService from '@/service/RentalService'
    import DocumentForm from './_Form.vue'
    
    export default {
        components: { DocumentForm },
        setup() {
            setMetaTitle('meta.title.rent_new_document')
            
            const rentalService = new RentalService()
            return {
                rentalService,
            }
        },
        data() {
            return {
                rental: {},
                document: {},
                errors: [],
                saving: false,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                        
                    ],
                }
            }
        },
        beforeMount() {
            this.rentalService.getDocument(this.$route.params.rentalId, this.$route.params.documentId)
                .then(
                    (response) => {
                        this.document = response.data
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
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                ]
                
                if(this.rental.full_number != undefined)
                {
                    items.push({'label' : this.rental.full_number, route : { name : 'rental_show'} })
                    items.push({'label' : this.$t('rent.edit_document'), disabled : true })
                }
                    
                return items
            },
            async updateDocument(document) {
                this.saving = true;
                this.rentalService.updateDocument(this.$route.params.rentalId, this.$route.params.documentId, document)
                    .then(
                        (response) => {
                            this.saving = false;
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('rent.document_updated'),
                            });
                            
                            this.$router.push({name: 'rental_show'})
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            this.errors = getResponseErrors(errors)
                            this.saving = false;
                        }
                    )
            },
            back() {
                this.$goBack('rental_show');
            },
            setRental(rental) {
                this.rental = rental;
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid pt-4">
                <Help show="rental" class="text-right mb-3"/>
                <DocumentForm @submit-form="updateDocument" @back="back" @set-rental="setRental" :document="document" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>