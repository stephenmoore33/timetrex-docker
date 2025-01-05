<script setup>
import { ref, onMounted } from 'vue';
import { CountryService } from '@/service/CountryService';

const countries = ref([]);
const cities = ref([
    { name: 'New York', code: 'NY' },
    { name: 'Rome', code: 'RM' },
    { name: 'London', code: 'LDN' },
    { name: 'Istanbul', code: 'IST' },
    { name: 'Paris', code: 'PRS' }
]);
const filteredCountries = ref(null);
const value1 = ref(null);
const value2 = ref(null);
const value3 = ref(null);
const value4 = ref(null);
const value5 = ref(null);
const value6 = ref(null);
const value7 = ref(null);
const value8 = ref(null);
const value9 = ref(null);
const value10 = ref(null);
const value11 = ref(null);
const value12 = ref(null);
const countryService = new CountryService();

onMounted(() => {
    countryService.getCountries().then((data) => (countries.value = data));
});

const searchCountry = (event) => {
    //in a real application, make a request to a remote url with the query and return filtered results, for demo we filter at client side
    const filtered = [];
    const query = event.query;
    for (let i = 0; i < countries.value.length; i++) {
        const country = countries.value[i];
        if (country.name.toLowerCase().indexOf(query.toLowerCase()) == 0) {
            filtered.push(country);
        }
    }
    filteredCountries.value = filtered;
};
</script>

<template>
    <div class="card">
        <h5>Float Label</h5>
        <p>All input text components support floating labels by adding <mark>(.p-float-label)</mark> to wrapper class.</p>
        <div class="grid p-fluid mt-3">
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <InputText type="text" id="inputtext" v-model="value1" />
                    <label for="inputtext">InputText</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <AutoComplete id="autocomplete" v-model="value2" :suggestions="filteredCountries" @complete="searchCountry($event)" field="name"></AutoComplete>
                    <label for="autocomplete">AutoComplete</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label p-input-icon-left">
                    <i class="pi pi-search"></i>
                    <InputText type="text" id="lefticon" v-model="value3" />
                    <label for="lefticon">Left Icon</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label p-input-icon-right">
                    <InputText type="text" id="righticon" v-model="value4" />
                    <label for="righticon">Right Icon</label>
                    <i class="pi pi-spin pi-spinner"></i>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <Calendar inputId="calendar" v-model="value5"></Calendar>
                    <label for="calendar">Calendar</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <Chips inputId="chips" v-model="value6"></Chips>
                    <label for="chips">Chips</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <InputMask id="inputmask" mask="99/99/9999" v-model="value7"></InputMask>
                    <label for="inputmask">InputMask</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <InputNumber id="inputnumber" v-model="value8"></InputNumber>
                    <label for="inputnumber">InputNumber</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">
                        <i class="pi pi-user"></i>
                    </span>
                    <span class="p-float-label">
                        <InputText type="text" id="inputgroup" v-model="value9" />
                        <label for="inputgroup">InputGroup</label>
                    </span>
                </div>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <Dropdown id="dropdown" :options="cities" v-model="value10" optionLabel="name"></Dropdown>
                    <label for="dropdown">Dropdown</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <MultiSelect id="multiselect" :options="cities" v-model="value11" optionLabel="name" :filter="false"></MultiSelect>
                    <label for="multiselect">MultiSelect</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <Textarea inputId="textarea" rows="3" cols="30" v-model="value12"></Textarea>
                    <label for="textarea">Textarea</label>
                </span>
            </div>
        </div>
    </div>
</template>
