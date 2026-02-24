
    <div class="container mx-auto py-8">
        <h2 class="text-2xl font-bold mb-6">رزرو نوبت</h2>

        <form id="booking-form" action="{{ route('customer.booking.store') }}" method="POST" class="booking-form">
            @csrf

            {{-- مرحله 1 --}}
            <div class="form-step" id="step-1">
                <h3 class="font-bold mb-4 text-lg">مرحله 1: انتخاب سرویس و زمان</h3>

                <div class="form-group">
                    <label>سرویس:</label>
                    <select name="service_id" id="service" class="form-input" required>
                        <option value="">انتخاب سرویس</option>
                        @foreach($Services as $Service)
                            <option value="{{$Service->id}}">{{$Service->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>قیمت:</label>
                    <input type="text" id="price" class="form-input" readonly placeholder="قیمت سرویس">
                </div>

                <div class="form-group">
                    <label>تاریخ رزرو:</label>
                    <input type="date" name="date" id="date" min="{{ date('Y-m-d') }}" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>ساعت رزرو:</label>
                    <div id="slots" class="slots-container"></div>
                    <input type="hidden" name="start_time" id="start_time">
                </div>

                <button type="button" class="next-btn">مرحله بعد</button>
            </div>

            {{-- مرحله 2 --}}
            <div class="form-step hidden" id="step-2">
                <h3 class="font-bold mb-4 text-lg">مرحله 2: اطلاعات شخصی</h3>

                <div class="form-group">
                    <label>نام کامل:</label>
                    <input type="text" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>موبایل:</label>
                    <input type="text" name="phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>ایمیل:</label>
                    <input type="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>رمز عبور:</label>
                    <input type="password" name="password" class="form-input" required>
                </div>

                <button type="button" class="prev-btn">مرحله قبل</button>
                <button type="submit" class="submit-btn">رزرو نوبت</button>
            </div>
        </form>
    </div>

    {{-- CSS --}}
    <style>
        .container { max-width: 600px; margin: auto; }
        .booking-form { background-color: #f9f9f9; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; transition: border 0.2s ease; }
        .form-input:focus { border-color: #007bff; outline: none; box-shadow: 0 0 5px rgba(0,123,255,0.3); }

        .slots-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
        .slot-btn { padding: 10px 15px; border: 1px solid #ccc; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s ease; min-width: 70px; text-align: center; font-size: 14px; }
        .bg-green-500 { background-color: #28a745; color: #fff; }
        .bg-green-500:hover { background-color: #218838; }
        .bg-red-500 { background-color: #dc3545; color: #fff; cursor: not-allowed; opacity: 0.6; }
        .bg-blue-500 { background-color: #007bff !important; color: #fff; border: 2px solid #0056b3; }

        .submit-btn, .next-btn, .prev-btn {
            background-color: #007bff; color: #fff; padding: 12px 20px; font-size: 16px; font-weight: 600;
            border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s ease; margin-top: 10px;
        }
        .submit-btn:hover, .next-btn:hover, .prev-btn:hover { background-color: #0056b3; }

        .form-step.hidden { display: none; }
    </style>

    {{-- JS --}}
    <script>
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const nextBtn = document.querySelector('.next-btn');
        const prevBtn = document.querySelector('.prev-btn');

        // حرکت بین مراحل
        nextBtn.addEventListener('click', () => {
            if (!document.getElementById('service').value || !document.getElementById('date').value || !start_time.value) {
                alert('لطفاً سرویس، تاریخ و ساعت را انتخاب کنید.');
                return;
            }
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
        });

        prevBtn.addEventListener('click', () => {
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
        });

        // ساعت رزرو
        const dateInput = document.getElementById('date');
        const slotsDiv = document.getElementById('slots');
        const startTimeInput = document.getElementById('start_time');

        dateInput.addEventListener('change', function() {
            const date = this.value;
            if (!date) { slotsDiv.innerHTML = ''; startTimeInput.value = ''; return; }

            fetch(`/slots?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    slotsDiv.innerHTML = '';
                    data.forEach(slot => {
                        const btn = document.createElement('button');
                        btn.type = 'button'; btn.textContent = slot.time; btn.className = 'slot-btn';
                        if (slot.available) {
                            btn.classList.add('bg-green-500');
                            btn.addEventListener('click', function() {
                                startTimeInput.value = slot.time;
                                document.querySelectorAll('#slots button').forEach(b => b.classList.remove('bg-blue-500'));
                                btn.classList.remove('bg-green-500');
                                btn.classList.add('bg-blue-500');
                            });
                        } else {
                            btn.classList.add('bg-red-500'); btn.disabled = true;
                        }
                        slotsDiv.appendChild(btn);
                    });
                });
        });

        // قیمت سرویس
        const serviceSelect = document.getElementById('service');
        const priceInput = document.getElementById('price');

        serviceSelect.addEventListener('change', function() {
            const serviceId = this.value;
            if (!serviceId) { priceInput.value = ''; return; }

            fetch(`/service-price?service_id=${serviceId}`)
                .then(res => res.json())
                .then(data => { priceInput.value = data.price + ' تومان'; })
                .catch(() => { priceInput.value = 'خطا در دریافت قیمت'; });
        });
    </script>

