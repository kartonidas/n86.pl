<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, p, getRentalBoxColor } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import RentalService from '@/service/RentalService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.rent_show')
            
            const rentalService = new RentalService()
            
            return {
                p,
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
            this.rentalService.get(this.$route.params.rentId)
                .then(
                    (response) => {
                        this.rental = response.data
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
            showRental() {
                this.$router.push({name: 'rental_show', params: { rentalId : this.$route.params.rentId }})
            }
        },
    }
</script>

<template v-if="!loading">
    <div class="card p-fluid mt-4">
        <div class="grid align-items-center">
            <div class="col-12 md:col-8 text-center text-lg line-height-3 flex-order-1 md:flex-order-0">
                {{ $t('rent.success_info_line_1') }}
                
                <div class="mt-3 mb-3 text-2xl">
                    <router-link :to="{name: 'rental_show', params: { rentalId : $route.params.rentId }}">
                        {{ rental.item.name }}
                    </router-link>
                </div>

                <div class="text-base mb-4">
                    {{ $t('rent.success_info_line_2') }} <br/> {{ rental.start }} -
                    <span v-if="rental.period == 'indeterminate'">
                        <span style="text-transform: lowercase">{{ $t('rent.indeterminate') }}</span>
                    </span>
                    <span v-if="rental.period == 'month'">
                        {{ rental.end }}<br/>({{ rental.months }} {{ p(rental.months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }})
                    </span>
                </div>
                
                <div class="grid justify-content-between">
                    <div class="col-12 sm:col-6 p-1 mt-1">
                        <Button severity="secondary" :label="$t('rent.new')" @click="showRental" class="align-center w-full" iconPos="right" icon="pi pi-plus"></Button>
                    </div>
                    <div class="col-12 sm:col-6 p-1 mt-1">
                        <Button severity="info" :label="$t('rent.details')" @click="showRental" class="align-center w-full" iconPos="right" icon="pi pi-external-link"></Button>
                    </div>
                </div>
            </div>
            <div class="col-12 md:col-4 text-center flex-order-0 md:flex-order-1">
                <i class="pi pi-check-circle text-green-700" style="font-size: 5rem;"></i>
            </div>
        </div>
    </div>
</template>