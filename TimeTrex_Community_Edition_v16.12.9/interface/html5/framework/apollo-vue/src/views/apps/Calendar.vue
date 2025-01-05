<script setup>
import { ref, onMounted } from 'vue';
import { EventService } from '@/service/EventService';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

onMounted(async () => {
    events.value = await getEvents();
    options.value = { ...options.value, ...{ events: events.value } };
    events.value.forEach((item) => tags.value.push(item.tag));
});
const tags = ref([]);
let clickedEvent = null;
const view = ref('display');
const showDialog = ref(false);
const changedEvent = ref({
    title: '',
    start: '',
    end: '',
    allDay: false,
    location: '',
    borderColor: '',
    textColor: '',
    description: '',
    tag: {
        name: 'Company A',
        color: '#FFB6B6'
    }
});
const options = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialDate: '2022-05-11',
    height: 720,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    editable: true,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    eventClick: (e) => onEventClick(e),
    select: (e) => onDateSelect(e)
});

const events = ref(null);
const eventService = ref(new EventService());

const getEvents = async () => {
    const response = eventService.value.getEvents();

    return response;
};

const onEventClick = (e) => {
    clickedEvent = e.event;
    let plainEvent = e.event.toPlainObject({ collapseExtendedProps: true, collapseColor: true });
    view.value = 'display';
    showDialog.value = true;

    changedEvent.value = { ...plainEvent, ...clickedEvent };
    changedEvent.value.start = clickedEvent.start;
    changedEvent.value.end = clickedEvent.end ? clickedEvent.end : clickedEvent.start;
};

const onEditClick = () => {
    view.value = 'edit';
};
const handleSave = () => {
    const isValidDate = changedEvent.value.start && changedEvent.value.end;
    if (!isValidDate) {
        return;
    }

    showDialog.value = false;
    clickedEvent = { ...changedEvent.value, backgroundColor: changedEvent.value.tag.color, borderColor: changedEvent.value.tag.color, textColor: '#212121' };

    setEvents();

    options.value = { ...options.value, ...{ events: events.value } };

    clickedEvent = null;
};

const onDateSelect = (e) => {
    view.value = 'new';
    showDialog.value = true;
    changedEvent.value = {
        ...e,
        title: '',
        location: '',
        borderColor: '',
        textColor: '',
        description: '',
        tag: {
            name: 'Company A',
            color: '#FFB6B6'
        }
    };
};

const setEvents = () => {
    const clickedEventHasId = Object.keys(clickedEvent).some((key) => key === 'id');
    if (clickedEventHasId) {
        events.value = events.value.map((i) => (i.id.toString() === clickedEvent.id.toString() ? (i = clickedEvent) : i));
    } else {
        events.value = [...events.value, { ...clickedEvent, id: Math.floor(Math.random() * 10000) }];
    }
};
</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Calendar</h5>
                <FullCalendar :events="events" :options="options" />

                <Dialog
                    v-model:visible="showDialog"
                    :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
                    :style="{
                        width: '36rem'
                    }"
                    modal
                    closable
                    @onHide="view = ''"
                >
                    <template #header>
                        <span class="text-900 font-semibold text-xl">{{ view === 'display' ? changedEvent.title : view === 'new' ? 'New Event' : 'Edit Event' }}</span>
                    </template>

                    <div v-if="view === 'display'">
                        <span class="text-900 font-semibold block mb-2">Description</span>
                        <span class="block mb-3">{{ changedEvent.description }}</span>

                        <div class="grid">
                            <div class="col-6">
                                <div class="text-900 font-semibold mb-2">Start</div>
                                <p class="flex align-items-center m-0">
                                    <i class="pi pi-fw pi-clock text-700 mr-2"></i>
                                    <span>{{ changedEvent.start.toISOString().slice(0, 10) }}</span>
                                </p>
                            </div>
                            <div class="col-6">
                                <div class="text-900 font-semibold mb-2">End</div>
                                <p class="flex align-items-center m-0">
                                    <i class="pi pi-fw pi-clock text-700 mr-2"></i>
                                    <span>{{ changedEvent.end.toISOString().slice(0, 10) }}</span>
                                </p>
                            </div>
                            <div class="col-12">
                                <div class="text-900 font-semibold mb-2">Location</div>
                                <p class="flex align-items-center m-0">
                                    <i class="pi pi-fw pi-clock text-700 mr-2"></i>
                                    <span>{{ changedEvent.location }}</span>
                                </p>
                            </div>
                            <div class="col-12">
                                <div class="text-900 font-semibold mb-2">Color</div>
                                <p class="flex align-items-center m-0">
                                    <span :style="{ 'background-color': changedEvent.color }" class="inline-flex flex-shrink-0 w-1rem h-1rem mr-2 border-circle"></span>
                                    <span>{{ changedEvent.tag.name }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-if="view !== 'display'">
                        <div class="grid p-fluid formgrid">
                            <div class="col-12 md:col-6 field">
                                <label for="title" class="text-900 font-semibold">Title</label>
                                <span class="p-input-icon-left">
                                    <i class="pi pi-pencil"></i>
                                    <InputText id="title" type="text" placeholder="Title" v-model="changedEvent.title" />
                                </span>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="location" class="text-900 font-semibold">Location</label>
                                <span class="p-input-icon-left">
                                    <i class="pi pi-map-marker"></i>
                                    <InputText id="location" type="text" placeholder="Location" v-model="changedEvent.location" />
                                </span>
                            </div>
                            <div class="col-12 field">
                                <label for="description" class="text-900 font-semibold">Event Description</label>
                                <Textarea id="description" type="text" :rows="5" v-model="changedEvent.description" style="resize: none"></Textarea>
                            </div>

                            <div class="col-12 md:col-6 field">
                                <label for="start" class="text-900 font-semibold">Start Date</label>
                                <Calendar dateFormat="mm-dd-yy" :max-date="changedEvent.end" showTime required inputId="start" v-model="changedEvent.start"></Calendar>
                            </div>
                            <div class="col-12 md:col-6 field">
                                <label for="start" class="text-900 font-semibold">End Date</label>
                                <Calendar dateFormat="mm-dd-yy" :minDate="changedEvent.start" showTime required inputId="end" v-model="changedEvent.end"></Calendar>
                            </div>
                            <div class="col-12 field">
                                <label for="company-color" class="text-900 font-semibold">Color</label>
                                <Dropdown inputId="company-color" :options="tags" v-model="changedEvent.tag" optionLabel="name">
                                    <template #option="slotProps">
                                        <div class="flex align-items-center">
                                            <div :style="{ 'background-color': slotProps.option.color }" class="flex-shrink-0 w-1rem h-1rem mr-2 border-circle"></div>
                                            <div>{{ slotProps.option.name }}</div>
                                        </div>
                                    </template>
                                    <template #value="slotProps">
                                        <div class="flex align-items-center">
                                            <div :style="{ 'background-color': slotProps.value.color }" class="flex-shrink-0 w-1rem h-1rem mr-2 border-circle"></div>
                                            <div>{{ slotProps.value.name }}</div>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>

                    <template #footer>
                        <Button v-if="view === 'display'" label="Edit" icon="pi pi-pencil" @click="onEditClick"></Button>
                        <Button v-if="view === 'new' || view === 'edit'" label="Save" icon="pi pi-check" @click="handleSave()" :disabled="!changedEvent.start || !changedEvent.end"></Button>
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
:deep(.fc-header-toolbar) {
    .fc-button {
        line-height: 1;
        min-height: 2.07rem;
    }
}
</style>
