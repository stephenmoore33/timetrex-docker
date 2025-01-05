<script setup>
import { ref } from 'vue';

const items = ref([
    {
        label: 'Update',
        icon: 'pi pi-refresh'
    },
    {
        label: 'Delete',
        icon: 'pi pi-times'
    },
    {
        separator: true
    },
    {
        label: 'Home',
        icon: 'pi pi-home'
    }
]);

const loading = ref([false, false, false]);

const load = (index) => {
    loading.value[index] = true;
    setTimeout(() => (loading.value[index] = false), 1000);
};
</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card">
                <h5>Default</h5>
                <div class="flex flex-wrap gap-2">
                    <Button label="Submit"></Button>
                    <Button label="Disabled" :disabled="true"></Button>
                    <Button label="Link" link />
                </div>
            </div>

            <div class="card">
                <h5>Severities</h5>
                <div class="flex flex-wrap gap-2">
                    <Button label="Primary" />
                    <Button label="Secondary" severity="secondary" />
                    <Button label="Success" severity="success" />
                    <Button label="Info" severity="info" />
                    <Button label="Warning" severity="warning" />
                    <Button label="Danger" severity="danger" />
                </div>
            </div>

            <div class="card">
                <h5>Text</h5>
                <div class="flex flex-wrap gap-2">
                    <Button label="Primary" text />
                    <Button label="Secondary" text severity="secondary" />
                    <Button label="Success" text severity="success" />
                    <Button label="Info" text severity="info" />
                    <Button label="Warning" text severity="warning" />
                    <Button label="Help" text severity="help" />
                    <Button label="Danger" text severity="danger" />
                    <Button label="Plain" text plain />
                </div>
            </div>

            <div class="card">
                <h5>Outlined</h5>
                <div class="flex flex-wrap gap-2">
                    <Button label="Primary" outlined />
                    <Button label="Secondary" outlined severity="secondary" />
                    <Button label="Success" outlined severity="success" />
                    <Button label="Info" outlined severity="info" />
                    <Button label="Warning" outlined severity="warning" />
                    <Button label="Help" outlined severity="help" />
                    <Button label="Danger" outlined severity="danger" />
                </div>
            </div>

            <div class="card">
                <h5>Button Group</h5>
                <div class="flex flex-wrap gap-2">
                    <span class="p-buttonset">
                        <Button label="Save" icon="pi pi-check" />
                        <Button label="Delete" icon="pi pi-trash" />
                        <Button label="Cancel" icon="pi pi-times" />
                    </span>
                </div>
            </div>

            <div class="card">
                <h5>SplitButton</h5>
                <div class="flex flex-wrap gap-2">
                    <SplitButton label="Save" icon="pi pi-check" :model="items" severity="secondary"></SplitButton>
                    <SplitButton label="Save" icon="pi pi-check" :model="items" severity="success"></SplitButton>
                    <SplitButton label="Save" icon="pi pi-check" :model="items" severity="info"></SplitButton>
                    <SplitButton label="Save" icon="pi pi-check" :model="items" severity="warning"></SplitButton>
                    <SplitButton label="Save" icon="pi pi-check" :model="items" severity="danger"></SplitButton>
                </div>
            </div>

            <div class="card">
                <h5>Templating</h5>
                <div class="flex flex-wrap gap-2">
                    <Button type="button">
                        <img alt="logo" src="/demo/images/logo-white.svg" style="width: 1.5rem" />
                    </Button>
                    <Button type="button" outlined severity="success">
                        <img alt="logo" src="/demo/images/logo.svg" style="width: 1.5rem" />
                        <span class="ml-2 text-bold">PrimeVue</span>
                    </Button>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card">
                <h5>Icons</h5>
                <div class="flex flex-wrap gap-2">
                    <Button icon="pi pi-star-fill"></Button>
                    <Button label="Bookmark" icon="pi pi-bookmark"></Button>
                    <Button label="Bookmark" icon="pi pi-bookmark" iconPos="right"></Button>
                </div>
            </div>

            <div class="card">
                <h5>Raised</h5>
                <div class="flex flex-wrap gap-2">
                    <Button label="Primary" raised />
                    <Button label="Secondary" raised severity="secondary" />
                    <Button label="Success" raised severity="success" />
                    <Button label="Info" raised severity="info" />
                    <Button label="Warning" raised severity="warning" />
                    <Button label="Danger" raised severity="danger" />
                </div>
            </div>

            <div class="card">
                <h5>Rounded</h5>
                <div class="flex flex-wrap gap-2">
                    <Button label="Primary" rounded />
                    <Button label="Secondary" rounded severity="secondary" />
                    <Button label="Success" rounded severity="success" />
                    <Button label="Info" rounded severity="info" />
                    <Button label="Warning" rounded severity="warning" />
                    <Button label="Danger" rounded severity="danger" />
                </div>
            </div>

            <div class="card">
                <h5>Rounded Icons</h5>
                <div class="flex flex-wrap gap-2">
                    <Button icon="pi pi-star-fill" rounded />
                    <Button icon="pi pi-bookmark" rounded severity="secondary" />
                    <Button icon="pi pi-check" rounded severity="success" />
                    <Button icon="pi pi-search" rounded severity="info" />
                    <Button icon="pi pi-user" rounded severity="warning" />
                    <Button icon="pi pi-sign-out" rounded severity="danger" />
                </div>
            </div>

            <div class="card">
                <h5>Rounded Text</h5>
                <div class="flex flex-wrap gap-2">
                    <Button icon="pi pi-check" rounded text />
                    <Button icon="pi pi-bookmark" rounded text severity="secondary" />
                    <Button icon="pi pi-search" rounded text severity="success" />
                    <Button icon="pi pi-user" rounded text severity="info" />
                    <Button icon="pi pi-bell" rounded text severity="warning" />
                    <Button icon="pi pi-heart" rounded text severity="help" />
                    <Button icon="pi pi-times" rounded text severity="danger" />
                    <Button icon="pi pi-filter" rounded text severity="plain" />
                </div>
            </div>

            <div class="card">
                <h5>Rounded Outlined</h5>
                <div class="flex flex-wrap gap-2">
                    <Button icon="pi pi-check" rounded outlined />
                    <Button icon="pi pi-bookmark" rounded outlined severity="secondary" />
                    <Button icon="pi pi-search" rounded outlined severity="success" />
                    <Button icon="pi pi-user" rounded outlined severity="info" />
                    <Button icon="pi pi-bell" rounded outlined severity="warning" />
                    <Button icon="pi pi-heart" rounded outlined severity="help" />
                    <Button icon="pi pi-times" rounded outlined severity="danger" />
                </div>
            </div>

            <div class="card">
                <h5>Loading</h5>
                <div class="flex flex-wrap gap-2">
                    <Button type="button" label="Search" icon="pi pi-search" :loading="loading[0]" @click="load(0)" />
                    <Button type="button" label="Search" icon="pi pi-search" iconPos="right" :loading="loading[1]" @click="load(1)" />
                    <Button type="button" icon="pi pi-search" :loading="loading[2]" @click="load(2)" />
                    <Button type="button" label="Search" :loading="loading[3]" @click="load(3)" />
                </div>
            </div>
        </div>
    </div>
</template>
