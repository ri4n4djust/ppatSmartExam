import axios from "axios";
import { defineStore } from "pinia";

export const useLaporanStore = defineStore("laporan", { 
    state: () => ({
        laporan: [],
        detail: []
    }),
    getters:{
        
    },
    actions: {
        async fetchLaporan() {
            try {
                const response = await axios.post("/api/laporan-admin");
                return this.laporan = response.data;
                // console.log(response.data)
                
            } catch (error) {
                console.error("Error fetching laporan:", error);
            }
        },
        async fetchDetail(exam){
            try {
                console.log(exam)
                const response = await axios.post("/api/hasil-ujian", {
                    exam_id : exam.exam_id,
                    siswa_id : exam.student_id
                })
                return this.detail = response.data
            } catch (error) {

            }
        }
    }
});
