<template>
    <div>
        <div class="form-group" :class="{ 'has-error': errors.name }">
            <label for="">Nama Item</label>
            <input type="text" class="form-control" v-model="product.name" placeholder="Kemeja">
            <p class="text-danger" v-if="errors.name">{{ errors.name[0] }}</p>
        </div>
        <div class="form-group" :class="{ 'has-error': errors.unit_type }">
            <label for="">Tipe</label>
            <select v-model="product.unit_type" class="form-control">
                <option value="">Pilih</option>
                <option value="Kilogram">Kilogram</option>
                <option value="Potong">Potong</option>
            </select>
            <p class="text-danger" v-if="errors.unit_type">{{ errors.unit_type[0] }}</p>
        </div>
        <div class="row">
            <div class="col-md-6">

                 <!-- KETIKA TOMBOL ADD NEW DITEKAN -->
                <div class="form-group" :class="{ 'has-error': errors.laundry_type }">
                    <label for="">Jenis Jasa <sup><a @click="showForm = true" href="javascript:void(0)" v-if="!showForm">Add New</a></sup></label>
                    <select v-model="product.laundry_type" class="form-control">
                        <option value="">Pilih</option>
                        <option v-for="(row, index) in laundry_types" :key="index" :value="row.id">{{ row.name }}</option>
                    </select>
                    <p class="text-danger" v-if="errors.laundry_type">{{ errors.laundry_type[0] }}</p>
                </div>
            </div>

            <!-- MAKA FORM UNTUK MENAMBAHKAN JENIS LAUNDRY AKAN DITAMPILKAN -->
            <div class="col-md-6" v-if="showForm">
                <div class="form-group" :class="{ 'has-error': errors.name_laundry_type }">
                    <label for="">&nbsp;</label>
                    <div class="input-group">
                        <input type="text" placeholder="Cuci Kering + Setrika" v-model="laundry_type" class="form-control">
                        <a href="javascript:void(0)" class="input-group-addon btn btn-warning btn-sm" id="basic-addon2" @click="addNewLaundryType">Save</a>
                    </div>
                    <p class="text-danger" v-if="errors.name_laundry_type">{{ errors.name_laundry_type[0] }}</p>
                </div>
            </div>
            <!-- END FORM ADD JENIS LAUNDRY -->
        </div>

        <div class="form-group" :class="{ 'has-error': errors.price }">
            <label for="">Harga</label>
            <input type="number" class="form-control" v-model="product.price">
            <p class="text-danger" v-if="errors.price">{{ errors.price[0] }}</p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group" :class="{ 'has-error': errors.service }">
                    <label for="">Lama Pengerjaan</label>
                    <input type="number" class="form-control" v-model="product.service">
                    <p class="text-danger" v-if="errors.service">{{ errors.service[0] }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" :class="{ 'has-error': errors.service_type }">
                    <label for="">Satuan</label>
                    <select class="form-control" v-model="product.service_type">
                        <option value="">Pilih</option>
                        <option value="Hari">Hari</option>
                        <option value="Jam">Jam</option>
                    </select>
                    <p class="text-danger" v-if="errors.service_type">{{ errors.service_type[0] }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
export default {
    name: 'FormCourier',
    created() {
        this.getLaundryType() // KETIKA COMPONENT DI-LOAD MAKA METHOD INI AKAN DIJALANKAN
        // APABILA MENGAKSESNYA DARI ROUTE EDIT
        if (this.$route.name == 'products.edit') {
            // MAKA FUNGSI UNTUK MENGAMBIL DATA PRODUCT BERDASARKAN ID AKAN DIJALANKAN
            this.editProduct(this.$route.params.id).then((res) => {
                // KEMUDIAN VARIABLE YANG ADA DI ISI DENGAN DATA DARI SERVER
                this.product = {
                    name: res.data.name,
                    unit_type: res.data.unit_type,
                    price: res.data.price,
                    laundry_type: res.data.laundry_type_id,
                    service: res.data.service,
                    service_type: res.data.service_type
                }
            })
        }
    },
    data() {
        return {
            // DEFINISIKAN VARIABLE
            product: {
                name: '',
                unit_type: '',
                price: '',
                laundry_type: '',
                service: '',
                service_type: ''
            },
            laundry_type: '',
            // DEFAULT FORM UNTUK MENAMBAHKAN JENIS LAUNDRY ADALAH FALSE, YANG BERARTI FORM TIDAK DITAMPILKAN
            showForm: false
        }
    },
    computed: {
        ...mapState(['errors']), // MENGAMBIL STATE ERRORS
        ...mapState('product', {
            laundry_types: state => state.laundry_types // MENGAMBIL STATE LAUNDRY_TYPES
        })
    },
    methods: {
        ...mapActions('product', ['getLaundryType', 'addLaundryType', 'addProductLaundry', 'editProduct', 'updateCourier']), // ME-LOAD SEMUA FUNGSI YANG ADA DI MODULE PRODUCT

        // CLEAR FORM KITA BUAT JADI METHOD BARU AGAR REUSABLE
        clearForm() {
            this.product = {
                name: '',
                unit_type: '',
                price: '',
                laundry_type: '',
                service: '',
                service_type: ''
            }
        },

        // FUNGSI YANG AKAN BERJALAN KETIKA TOMBOL SAVE DARI ADD JENIS LAUNDRY DITEKAN
        addNewLaundryType() {
            // MENGIRIMKAN PERMINTAAN KE SERVER UNTUK DISIMPAN
            this.addLaundryType({ name_laundry_type: this.laundry_type }).then(() => {
                // MENGAMBUL DATA TERBARU DARI SERVER
                this.getLaundryType().then(() => {
                    // FORM DI SERT FALSE KEMBALI
                    this.showForm = false
                    this.laundry_type = '' // DAN LAUNDRY_TYPE DI KOSONGKAN
                })
            })
        },
        // KETIKA TOMBOL ADD NEW DARI FILE ADD, VUE DI TEKAN, MAKA FUNGSI INI AKAN DIJALANKAN
        submit() {
            // APABILA DIAKSESNYA DARI ROUTE DENGAN NAME PRODUCTS.ADD
            if (this.$route.name == 'products.add') {
                // MAKA FUNGSI INI AKAN DIJALANKAN UNTUK MENAMBAHKAN PRODUCT BARU
                this.addProductLaundry(this.product).then(() => {
                    // KOSONGKAN VARIABLE KETIKA BERHASIL MENYIMPAN DATA KE SERVER
                    this.product = {
                        name: '',
                        unit_type: '',
                        price: '',
                        laundry_type: ''
                    }

                    this.clearForm()
                    // REDIRECT KEMBALI KE HALAMAN LIST PRODUCT
                    this.$router.push({ name: 'products.data' })
                })
                // KETIKA DIAKSES MELALUI ROUTE DENGAN NAME PRODUCTS.EDIT
            } else if (this.$route.name == 'products.edit') {
                // MAKA ID AKAN DI TAMPILKAN KE DALAM OBJECT VARIABLE PRODUCT
                Object.assign(this.product, { id: this.$route.params.id })
                // KIRIM PERMINTAAN KE SERVER UNTUK MENGUBAH DATA
                this.updateCourier(this.product).then(() => {
                    // KOSONGKAN VARIABLE
                    this.product = {
                        name: '',
                        unit_type: '',
                        price: '',
                        laundry_type: ''
                    }
                    this.clearForm()
                    // REDIRECT KEMBALI
                    this.$router.push({ name: 'products.data' })
                })
            }
        }
    }
}
</script>
