<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard & Meals Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans p-6">

    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-5 rounded-lg shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800">لوحة التحكم وإدارة الوجبات</h1>
            <button onclick="openModal('createModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                + إضافة وجبة جديدة
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border-r-4 border-blue-500">
                <p class="text-sm text-gray-500">إجمالي المصروفات الشهرية</p>
                <h3 class="text-xl font-bold text-gray-800 mt-1" id="monthlySpend">0.00 ج.م</h3>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-r-4 border-green-500">
                <p class="text-sm text-gray-500">إجمالي التوفير</p>
                <h3 class="text-xl font-bold text-gray-800 mt-1" id="totalSavings">0.00 ج.م</h3>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-r-4 border-yellow-500">
                <p class="text-sm text-gray-500">عدد الطلبات هذا الشهر</p>
                <h3 class="text-xl font-bold text-gray-800 mt-1" id="ordersCount">0</h3>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-r-4 border-purple-500">
                <p class="text-sm text-gray-500">نقاط الولاء</p>
                <h3 class="text-xl font-bold text-gray-800 mt-1" id="loyaltyPoints">0</h3>
            </div>
        </div>

        <!-- Meals Table (CRUD) -->
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h2 class="text-lg font-bold text-gray-800 mb-4">قائمة الوجبات والمنتجات</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 border-b">
                            <th class="p-3">#ID</th>
                            <th class="p-3">العنوان</th>
                            <th class="p-3">السعر</th>
                            <th class="p-3">الخصم</th>
                            <th class="p-3 text-center">العمليات</th>
                        </tr>
                    </thead>
                    <tbody id="mealsTableBody" class="divide-y text-gray-700">
                        <!-- Data loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal: Create Meal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 space-y-4">
            <h3 class="text-lg font-bold text-gray-800">إضافة وجبة جديدة</h3>
            <form id="createMealForm" onsubmit="handleCreate(event)" class="space-y-3">
                <input type="text" name="title" placeholder="عنوان الوجبة" required class="w-full p-2 border rounded">
                <input type="number" name="category_id" placeholder="رقم القسم (Category ID)" required class="w-full p-2 border rounded">
                <input type="number" step="0.01" name="price" placeholder="السعر" required class="w-full p-2 border rounded">
                <input type="number" step="0.01" name="discount_price" placeholder="السعر بعد الخصم (اختياري)" class="w-full p-2 border rounded">
                <textarea name="description" placeholder="الوصف" class="w-full p-2 border rounded"></textarea>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 bg-gray-200 rounded">إلغاء</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">حفظ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Meal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 space-y-4">
            <h3 class="text-lg font-bold text-gray-800">تعديل الوجبة</h3>
            <form id="editMealForm" onsubmit="handleUpdate(event)" class="space-y-3">
                <input type="hidden" id="edit_meal_id">
                <input type="text" id="edit_title" placeholder="عنوان الوجبة" class="w-full p-2 border rounded">
                <input type="number" step="0.01" id="edit_price" placeholder="السعر" class="w-full p-2 border rounded">
                <input type="number" step="0.01" id="edit_discount_price" placeholder="السعر بعد الخصم" class="w-full p-2 border rounded">
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 bg-gray-200 rounded">إلغاء</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">تحديث</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        // Fetch Dashboard Analytics
        async function fetchDashboard() {
            try {
                const res = await fetch('/api/dashboard');
                const result = await res.json();
                if(result.success) {
                    const insights = result.data.shopping_insights;
                    const overview = result.data.overview;
                    document.getElementById('monthlySpend').innerText = insights.monthly_spend + ' ج.م';
                    document.getElementById('totalSavings').innerText = insights.total_savings + ' ج.م';
                    document.getElementById('ordersCount').innerText = insights.orders_this_month.count;
                    document.getElementById('loyaltyPoints').innerText = overview.loyalty_points;
                }
            } catch (err) { console.error(err); }
        }

        // Handle Create
        async function handleCreate(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            const res = await fetch('/api/dashboard/meals', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            if(res.ok) {
                closeModal('createModal');
                alert('تمت الإضافة بنجاح!');
            }
        }

        // Handle Delete
        async function handleDelete(id) {
            if(!confirm('هل أنت تأكد من مسح الوجبة؟')) return;
            const res = await fetch(`/api/dashboard/meals/${id}`, { method: 'DELETE' });
            if(res.ok) alert('تم الحذف بنجاح!');
        }

        fetchDashboard();
    </script>
</body>
</html>