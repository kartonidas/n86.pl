<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, timeToDate } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Rental from '@/views/app/_partials/Rental.vue'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address, Rental },
        setup() {
            setMetaTitle('meta.title.rent_show')
            
            const rentalService = new RentalService()
            
            return {
                rentalService,
                hasAccess,
                getValueLabel,
                timeToDate
            }
        },
        data() {
            return {
                errors: [],
                rental: {
                    item: {},
                    tenant: {},
                },
                loading: true,
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                        {'label' : this.$t('rent.details'), disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rental = response.data
                        this.loading = false
                    },
                    (errors) => {
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1" v-if="!loading">
        <div class="col col-12">
            <div class="grid mt-1">
                <div class="col col-12">
                    <Card class="mb-3">
                        <template #content pt="item">
                            <Rental :object="rental" />
                            
                            <div class="mt-5">
                                <strong>TODO</strong>
                                <ul>
                                    <li>Komponen Rental - dodać opcję source=details i nie wyswietlac 'przejdz do szczegółów'</li>
                                    <li>Uzupełnienie informacji</li>
                                    <li>Generowanie dokumentów (umowa, aneks, protokół zdawczo odbiorczy)</li>
                                    <li>Wypowiedzenie (umowa cykliczna)</li>
                                    <li>Lista wpłat / rachunków</li>
                                    <li>Edycja danych (tylko podstwawoe informacje, jak czynsz, data trwania, okres wypowiedzenia)</li>
                                    <li>Historia edycji?</li>
                                </ul>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>