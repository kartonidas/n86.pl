<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
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
            <div class="card">
                <div class="grid">
                    <div class="col-12 xl:col-6">
                        <div class="flex justify-content-between align-items-center mb-5">
                            <h4 class="inline-flex mb-0 text-color font-medium">Nieruchomość</h4>
                        </div>
                        
                        <Badge :value="getValueLabel('item_types', rental.item.type)" class="font-normal" severity="info"></Badge>
                        <div class="font-medium text-lg mb-2 mt-1">{{ rental.item.name }}</div>
                        <Address :object="rental.item" :newline="true"/>
                    </div>
                    <div class="col-12 xl:col-6">
                        <div class="flex justify-content-between align-items-center mb-5">
                            <h4 class="inline-flex mb-0 text-color font-medium">Najemca</h4>
                        </div>
                        
                        <Rental :object="rental" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div v-if="rental.status == 'current'">
        WYPOWIEDZ UMOWE!
    </div>
    
    
    <div class="mt-5">
        <strong>TODO</strong>
        <ul>
            <li>Uzupełnienie informacji</li>
            <li>Generowanie dokumentów (umowa, aneks, protokół zdawczo odbiorczy)</li>
            <li>Wypowiedzenie (umowa cykliczna)</li>
            <li>Lista wpłat / rachunków</li>
            <li>Edycja danych (tylko podstwawoe informacje, jak czynsz, data trwania, okres wypowiedzenia)</li>
            <li>Historia edycji?</li>
        </ul>
    </div>
</template>