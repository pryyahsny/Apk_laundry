<template>
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6" v-if="transaction.status == 0">
                        <div class="col-md-6" v-if="transaction.status == 0">
                            <h4>Payment</h4>
                            <hr>
                            <div class="form-group">
                                <label for="">Tagihan</label>
                                <input type="text" :value="transaction.amount" class="form-control" readonly>
                            </div>

                            <div class="form-group" v-if="transaction.customer && transaction.customer.deposit >= transaction.amount">
                                <input type="checkbox" v-model="via_deposit"> Bayar Via Deposit?
                            </div>

                            <div class="form-group">
                                <label for="">Jumlah Bayar</label>
                                <input type="number" class="form-control" v-model="amount">
                            </div>
                            <p v-if="isCustomerChange">Kembalian: Rp {{ customerChangeAmount }}</p>
                            <div class="form-group" v-if="isCustomerChange">
                                <input type="checkbox" v-model="customer_change" id="customer_change">
                                <label for="customer_change"> Kembalian Jadi Deposit?</label>
                            </div>
                            <p class="text-danger" v-if="payment_message">{{ payment_message }}</p>
                            <button class="btn btn-primary btn-sm" :disabled="loading" @click="makePayment">Bayar</button>
                        </div>

                    </div>
                    <div class="col-md-6" v-if="transaction.customer">
                      	<!-- MENAMPILKAN DETAIL INFORMASI CUSTOMER TERKAIT -->
                        <h4>Customer Info</h4>
                        <hr>
                        <table>
                            <tr>
                                <th width="30%">NIK </th>
                                <td width="5%">:</td>
                                <td>{{ transaction.customer.nik }}</td>
                            </tr>
                            <tr>
                                <th>No Telp </th>
                                <td>:</td>
                                <td>{{ transaction.customer.phone }}</td>
                            </tr>
                            <tr>
                                <th>Alamat </th>
                                <td>:</td>
                                <td>{{ transaction.customer.address }}</td>
                            </tr>
                            <tr>
                                <th>Deposit </th>
                                <td>:</td>
                                <td>Rp {{ transaction.customer.deposit }}</td>
                            </tr>
                            <tr>
                                <th>Point </th>
                                <td>:</td>
                                <td>{{ transaction.customer.point }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6" v-if="transaction.payment">
                        <!-- MENAMPILKAN RIWAYAT PEMBAYARAN ORDERAN TERSEBUT -->
                        <h4>Riwayat Pemabayaran</h4>
                        <hr>
                        <table>
                            <tr>
                                <th width="30%">Jumlah Pembayaran </th>
                                <td width="5%">:</td>
                                <td>Rp {{ transaction.payment.amount }}</td>
                            </tr>
                            <tr>
                                <th>Kembalian </th>
                                <td>:</td>
                                <td>Rp {{ transaction.payment.customer_change }}</td>
                            </tr>
                            <tr>
                                <th>Metode Pembayaran </th>
                                <td>:</td>
                                <td>{{ transaction.payment.type_label }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-12" style="padding-top: 20px">
                        <div class="alert alert-success" v-if="payment_success">Pembayaran Berhasil</div>

                        <h4>Detail Transaction</h4>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Paket</th>
                                        <th width="28%">Waktu Layanan</th>
                                        <th>Berat/Satuan</th>
                                        <th>Harga</th>
                                        <th>Subtotal</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  	<!-- LOOPING DETAIL TRANSAKSI -->
                                    <tr v-for="(row, index) in transaction.detail" :key="index">
                                        <td>
                                            <strong>{{ row.product.name }}</strong>
                                            <sup v-html="row.status_label"></sup>
                                        </td>
                                        <td>{{ row.service_time }}</td>
                                        <td>
                                            {{ row.qty }} ({{ row.product.unit_type }})
                                        </td>
                                        <td>Rp {{ row.price }} / {{ row.product.unit_type }}</td>
                                        <td>Rp {{ row.subtotal }}</td>
                                        <td>
                                            <!-- TOMBOL UNTUK MENYELESAIKAN SETIAP PESANAN -->
                                          	<!-- TOMBOL INI DITAMPILKAN KETIKA PEMBAYARAN SUDAH DILAKUKAN DAN STATUSNYA MASIH PROSES -->
                                            <button class="btn btn-success btn-sm" v-if="transaction.status == 1  && row.status == 0" @click="isDone(row.id)">
                                              	<!-- KETIKA DIKLIK MAKA AKAN MENJALANKAN FUNGSI isDone() DAN MENGIRIMKAN PARAMETER ID DETAIL TRANSAKSI -->
                                                <i class="fa fa-paper-plane-o"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapActions, mapState, mapMutations } from 'vuex'
    export default {
        name: 'DetailTransaction',
        created() {
            //KETIKA COMPONENT DI-LOAD, MAKA KITA LOAD DATA TRANSAKSI BERDASARKAN ID TRANSAKSI YANG DI DAPATKAN DARI URL
            this.detailTransaction(this.$route.params.id)
        },
        data() {
            //DEFINISIKAN VARIABLE YANG NNTINYA DIGUNAKAN UNTUK PAYMENT
            return {
                amount: null,
                customer_change: false,
                loading: false,
                payment_message: null,
                payment_success: false,
                via_deposit: false
            }
        },
        watch: {
            //JIKA VALUE NYA BERUBAH
            via_deposit() {
                //CEK JIKA TRUE
                if (this.via_deposit) {
                    //MAKA TOTAL PEMBAYARAN DISET SEJUMLAH TAGIHAN
                    this.amount = this.transaction.amount
                } else {
                    //JIKA FALSE MAKA DI SET NULL KEMBALI
                    this.amount = null
                }
            }
        },
        computed: {
            ...mapState('transaction', {
                //MENGAMBIL DATA TRANSAKSI YANG TELAH DISIMPAN KE DALAM STATE TRANSACTION
               transaction: state => state.transaction
            }),
            isCustomerChange() {
                return this.amount > this.transaction.amount  //BERNILAI TRUE/FALSE SESUAI KONDISINYA
            },
            customerChangeAmount() {
                return parseInt(this.amount - this.transaction.amount) //SELISIH ANTARA TAGIHAN DAN JUMLAH YANG DIBAYARKAN
            }
        },
        methods: {
            ...mapActions('transaction', ['detailTransaction', 'completeItem']),
            makePayment() {
                //JIKA JUMLAH PEMBAYARAN KURANG DARI TAGIHAN
                if (this.amount < this.transaction.amount) {
                    //MAKA SET NOTIF ERROR
                    this.payment_message = 'Pembayaran Kurang Dari Tagihan'
                    //HENTIKAN PROSES
                    return
                }

                //SELAIN ITU, MAKA SET LOADING JADI TRUE
                this.loading = true
                //BUAT REQUEST KE SERVER
                this.payment({
                    //DENGAN MENGIRIMKAN PARAMETER BERIKUT
                    transaction_id: this.$route.params.id,
                    amount: this.amount,
                    customer_change: this.customer_change,
                    via_deposit: this.via_deposit
                }).then(() => {
                   // JIKA PEMBAYARAN BERHASIL
                   if (res.status == 'success') {
                       // ALERT DAN SEMUA VARIABLE DI RESET
                       this.payment_success = true
                       setTimeout(() => {
                           this.loading = false,
                           this.amount = null,
                           this.customer_change = false,
                           this.payment_message = null,
                           this.via_deposit = false
                       }, 500)
                       this.detailTransaction(this.$route.params.id)
                   } else {
                       // JIKA GAGAL MAKA TAMPILKAN ALERT GAGAL
                       this.loading = false
                       alert(res.data)
                   }
                })
            },
            //KETIKA TOMBOL MASING-MASING PESANAN DIKLIK
            isDone(id) {
                //MAKA KITA MENAMPILKAN ALERT KONFIRMASI
                this.$swal({
                    title: 'Kamu Yakin?',
                    text: "Akan menyelesaikan pesanan ini!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Iya, Lanjutkan!'
                }).then((result) => {
                    if (result.value) {
                        //JIKA SETUJU MAKA KIRIM PERMINTAAN KE SERVER
                        this.completeItem({ id: id }).then(() => {
                            //JIKA BERHASIL, MAKA LOAD DATA TRASANSAKSI TERBARU
                            this.detailTransaction(this.$route.params.id)
                        })
                    }
                })
            }
        }
    }
</script>
