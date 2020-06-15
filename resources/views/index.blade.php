<!doctype html>
<html xmlns:v-on="http://www.w3.org/1999/xhtml" xmlns:v-bind="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bárdi Autó tesztfeladat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.15.2/axios.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>

<body>
    <div class="container" id="app">
        <div class="row">

            <div class="col-12">
                <h1>${title}</h1>
                <hr />
            </div>

            <!-- Székek listázása (percenként frissül) -->
            <div class="col-md-8">
                <div class="row text-center">
                    <div class="col-sm-3 p-2" v-for="item in seats">
                        <div class="card border">
                            <h3>${item.row}. sor ${item.serial}. szék</h3>
                            <button v-bind:class="[item.status == seat_status.free ? 'btn btn-sm btn-success' : 'btn btn-sm btn-default']" v-on:click="reserve(item)">
                                <span v-if="item.status == seat_status.free">Szabad</span>
                                <span v-else-if="item.status < seat_status.ordered">Foglalt</span>
                                <span v-else>Elkelt</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Foglalások (csak akkor jelenik meg, ha van saját foglalás) -->
            <form class="col-md-4" v-if="reservations.length" v-on:submit.prevent="sendOrder()">
                <h2>Foglalások</h2>

                <!-- Időzítő (0-hoz érve törlődnek a saját foglalások) -->
                <hr/>
                <div class="row">
                    <div class="col-12">A foglalások ${remaining_time} után automatikusan visszavonásra kerülnek.</div>
                </div>

                <!-- Saját foglalások listája -->
                <hr/>
                <div v-for="item in reservations" class="row pb-2">
                    <div class="col-6">${item.row}. sor ${item.serial}. szék</div>
                    <div class="col-4">${item.price} Ft</div>
                    <div class="col-2 text-right">
                        <button type="button" class="btn btn-danger btn-sm" v-on:click="revoke(item)"><i class="fas fa-trash"></i></button>
                    </div>
                </div>

                <!-- Fizetendő összeg -->
                <hr/>
                <div class="row">
                    <div class="col-6">Fizetendő: </div>
                    <div class="col-4 font-weight-bold">${totalPrice} Ft</div>
                </div>

                <!-- Személyes adatok a rendelés véglegesítéséhez -->
                <hr/>
                <div class="form-group">
                    <label>Vezetéknév:</label>
                    <input type="text" class="form-control input-sm" v-model="order.lastName" required="required" />
                </div>
                <div class="form-group">
                    <label>Keresztnév:</label>
                    <input type="text" class="form-control input-sm" v-model="order.firstName" required="required" />
                </div>
                <div class="form-group">
                    <label>E-mail:</label>
                    <input type="email" class="form-control input-sm" v-model="order.email" required="required" />
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm form-control"><i class="fas fa-credit-card"></i> Foglalás véglegesítése</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var app;
            app = new Vue({
                delimiters: ['${', '}'],
                el: '#app',
                data: {
                    title: 'Bárdi Autó tesztfeladat',
                    seats: null,
                    order: { firstName: '', lastName: '', email: '' },
                    transaction_code: null,
                    seat_status: null,
                    remaining_timer: {
                        seconds: null,
                        max_seconds: null,
                        timeout: null,
                    },
                    refresh_timer: {
                        minutes_to_page_reload: 15,
                        timeout: null,
                    }
                },
                created: function () {
                    // beállítások letöltése
                    this.getSettings();

                    // Széklista frissítése percenként
                    let step = this.refresh_timer.minutes_to_page_reload;
                    const $this = this;
                    const timer = function () {
                        $this.getSeats(function () {
                            step--;
                            if (step) $this.refresh_timer.timeout = setTimeout(timer, 60000);
                            else document.location.reload();
                        });
                    };
                    timer();
                },
                computed: {
                    // Saját foglalások
                    reservations: function () {
                        if (!this.seats) return [];

                        let arr = [];
                        for (i = 0; i < this.seats.length; i++) {
                            if (this.seats[i].status == this.seat_status.own_reservation) arr.push(this.seats[i]);
                        }
                        return arr;
                    },
                    // Fizetendő összeg
                    totalPrice: function () {
                        let total = 0;
                        for (i = 0; i < this.reservations.length; i++) {
                            total += this.reservations[i].price;
                        }
                        return total;
                    },
                    // Hátralevő idő a foglalások automatikus törléséig
                    remaining_time: function () {
                        let minutes = Math.floor(this.remaining_timer.seconds / 60);
                        let seconds = this.remaining_timer.seconds - (minutes * 60);
                        if (minutes < 10) minutes = '0' + minutes;
                        if (seconds < 10) seconds = '0' + seconds;
                        return minutes + ':' + seconds;
                    }
                },
                methods: {
                    // Foglalás időzítő indítása
                    // 0-hoz érve leáll és frissíti a beállításokat, ezzel egyidejüleg törli a foglalásokat
                    startRemainingTimer: function (start_seconds) {
                        this.stopRemainingTimer();
                        this.remaining_timer.seconds = start_seconds ? start_seconds : this.remaining_timer.max_seconds;

                        const $this = this;
                        const timer = function () {
                            $this.remaining_timer.seconds--;
                            if ($this.remaining_timer.seconds > 0) {
                                $this.remaining_timer.timeout = setTimeout(timer, 1000);
                            }
                            else {
                                $this.stopRemainingTimer();
                                $this.getSettings();
                            }
                        };
                        this.remaining_timer.timeout = setTimeout(timer, 1000);
                    },

                    // Foglalás időzítő leállítása
                    stopRemainingTimer: function () {
                        if (this.remaining_timer.timeout) {
                            this.remaining_timer.seconds = 0;
                            clearTimeout(this.remaining_timer.timeout);
                        }
                    },

                    // Beállítások letöltése
                    getSettings: function () {
                        const $this = this;
                        $this.transaction_code = localStorage.getItem('transaction_code');
                        axios.get('/settings/' + ($this.transaction_code ? $this.transaction_code : 0)).then(function (response) {
                            // tranzakciós kód beállítás
                            if (response.data.transaction.is_new) {
                                localStorage.setItem('transaction_code', response.data.transaction.code);
                                $this.transaction_code = response.data.transaction.code;
                            }

                            // szék státuszok, foglalás időzítő beállítások
                            $this.seat_status = response.data.seat_status;
                            $this.remaining_timer.seconds = response.data.remaining_time.seconds;
                            $this.remaining_timer.max_seconds = response.data.remaining_time.max_seconds;

                            // széklista frissítés
                            // ha van saját foglalás, foglalás időzítő indítása
                            $this.getSeats(function () {
                                if ($this.remaining_timer.seconds > 0) {
                                    $this.startRemainingTimer($this.remaining_timer.seconds);
                                }
                            });
                        });
                    },

                    // Széklista letöltés
                    getSeats: function (completeCallback) {
                        const $this = this;
                        axios.get('/seats/' + $this.transaction_code).then(function (response) {
                            $this.seats = response.data;
                            if (completeCallback) completeCallback();
                        });
                    },

                    // Foglalás (csak szabad státusz esetén lehetséges)
                    reserve: function (seat) {
                        if (seat.status != this.seat_status.free) return;

                        const $this = this;
                        axios.post('/reserve', {
                            id: seat.id,
                            transaction_code: $this.transaction_code
                        }).then(function (response) {
                            if (response.data.status > $this.seat_status.own_reservation) {
                                alert('Sikertelen foglalás, ez a szék már ' + (response.data.status == $this.seat_status.ordered ? 'elkelt' : 'foglalt'));
                            }
                            $this.getSeats(function () {
                                $this.startRemainingTimer();
                            });
                        });
                    },

                    // Foglalás visszavonása (csak saját foglalásnál lehetséges)
                    revoke: function (seat) {
                        if (seat.status != this.seat_status.own_reservation) return;

                        const $this = this;
                        axios.post('/revoke', {
                            id: seat.id,
                            transaction_code: $this.transaction_code
                        }).then(function (response) {
                            if (response.data.status != $this.seat_status.free)
                                alert('Sikertelen visszavonás, ez a szék már ' + (response.data.status == $this.seat_status.ordered ? 'elkelt' : 'foglalt'));

                            $this.getSeats(function () {
                                if ($this.reservations.length > 0) $this.startRemainingTimer();
                                else $this.stopRemainingTimer();
                            });
                        });
                    },

                    // Rendelés elküldése
                    sendOrder: function () {
                        if (!this.reservations.length) return false;

                        const $this = this;
                        axios.post('/order', {
                            transaction_code: $this.transaction_code,
                            order: $this.order,
                            reservations: $this.reservations
                        }).then(function (response) {
                            $this.order.firstName = '';
                            $this.order.lastName = '';
                            $this.order.email = '';
                            $this.getSettings();
                        });
                    }
                }
            });
        })();
    </script>
</body>
</html>
