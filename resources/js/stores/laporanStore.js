import axios from "axios";
import { defineStore } from "pinia";

export const useLaporanStore = defineStore("laporan", { 
    state: () => ({
        laporan: []
    }),
    actions: {
        async fetchLaporan() {
            try {
                const response = await axios.get("/api/laporan");
                this.laporan = response.data;
            } catch (error) {
                console.error("Error fetching laporan:", error);
            }
        }
    }
});
