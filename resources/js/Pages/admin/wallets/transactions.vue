<template>
  <div>
    <div class="page-content">
      <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
          <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
              <h4 class="mb-sm-0">Wallet Transactions</h4>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="card border-0 bg-soft-success">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri-arrow-up-circle-fill text-white fs-5"></i>
                  </div>
                  <div class="ms-3">
                    <p class="text-muted mb-0">Total Credits</p>
                    <h5 class="text-success mb-0">₹{{ formatCurrency(summary.total_credit) }}</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 bg-soft-danger">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri-arrow-down-circle-fill text-white fs-5"></i>
                  </div>
                  <div class="ms-3">
                    <p class="text-muted mb-0">Total Debits</p>
                    <h5 class="text-danger mb-0">₹{{ formatCurrency(summary.total_debit) }}</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 bg-soft-primary">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri-file-list-3-fill text-white fs-5"></i>
                  </div>
                  <div class="ms-3">
                    <p class="text-muted mb-0">Total Transactions</p>
                    <h5 class="text-primary mb-0">{{ summary.total_transactions }}</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
          <div class="col-md-2">
            <select class="form-select" v-model="filters.type" @change="applyFilters">
              <option value="">All Types</option>
              <option value="topup">Top-up</option>
              <option value="platform_fee">Platform Fee</option>
              <option value="refund">Refund</option>
              <option value="admin_adjustment">Admin Adjustment</option>
              <option value="promotional_credit">Promotional</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.direction" @change="applyFilters">
              <option value="">All Directions</option>
              <option value="credit">Credit</option>
              <option value="debit">Debit</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.status" @change="applyFilters">
              <option value="">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="date" class="form-control" v-model="filters.from_date" @change="applyFilters" placeholder="From Date">
          </div>
          <div class="col-md-2">
            <input type="date" class="form-control" v-model="filters.to_date" @change="applyFilters" placeholder="To Date">
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary" @click="applyFilters">Apply</button>
            <button class="btn btn-soft-secondary ms-1" @click="resetFilters">Reset</button>
          </div>
        </div>

        <!-- Transactions Table -->
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-nowrap table-align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="tx in transactions.data" :key="tx.id">
                        <td>
                          <div>
                            <h6 class="mb-0">{{ tx.user?.name || tx.wallet?.user?.name }}</h6>
                            <small class="text-muted">{{ tx.user?.email || tx.wallet?.user?.email }}</small>
                          </div>
                        </td>
                        <td>
                          <span class="badge" :class="getTypeBadgeClass(tx.type)">
                            {{ tx.type?.replace('_', ' ') }}
                          </span>
                        </td>
                        <td>
                          <span :class="tx.direction === 'credit' ? 'text-success' : 'text-danger'" class="fw-bold">
                            {{ tx.direction === 'credit' ? '+' : '-' }}₹{{ formatCurrency(tx.amount) }}
                          </span>
                        </td>
                        <td>₹{{ formatCurrency(tx.amount) }}</td>
                        <td class="text-muted">₹{{ formatCurrency(tx.balance_before) }}</td>
                        <td class="text-muted">₹{{ formatCurrency(tx.balance_after) }}</td>
                        <td>
                          <span class="badge" :class="getStatusBadgeClass(tx.status)">
                            {{ tx.status }}
                          </span>
                        </td>
                        <td>
                          <small class="text-muted" v-if="tx.reference_type">
                            {{ tx.reference_type?.split('\\').pop() }} #{{ tx.reference_id }}
                          </small>
                        </td>
                        <td>{{ formatDate(tx.created_at) }}</td>
                      </tr>
                      <tr v-if="transactions.data?.length === 0">
                        <td colspan="9" class="text-center py-4 text-muted">No transactions found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3" v-if="transactions.last_page > 1">
                  <div class="text-muted">
                    Showing {{ transactions.from }} to {{ transactions.to }} of {{ transactions.total }} entries
                  </div>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !transactions.prev_page_url }">
                      <a class="page-link" @click="goToPage(transactions.current_page - 1)">Previous</a>
                    </li>
                    <li class="page-item" v-for="page in visiblePages" :key="page" :class="{ active: page === transactions.current_page }">
                      <a class="page-link" @click="goToPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: !transactions.next_page_url }">
                      <a class="page-link" @click="goToPage(transactions.current_page + 1)">Next</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue2';

const props = defineProps({
  transactions: Object,
  summary: Object,
  filters: Object,
});

const filters = ref({
  type: props.filters?.type || '',
  direction: props.filters?.direction || '',
  status: props.filters?.status || '',
  from_date: props.filters?.from_date || '',
  to_date: props.filters?.to_date || '',
});

const visiblePages = computed(() => {
  const pages = [];
  const current = props.transactions.current_page;
  const last = props.transactions.last_page;
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const applyFilters = () => {
  router.get('/admin/wallet-transactions', filters.value, { preserveState: true });
};

const resetFilters = () => {
  filters.value = { type: '', direction: '', status: '', from_date: '', to_date: '' };
  applyFilters();
};

const goToPage = (page) => {
  router.get('/admin/wallet-transactions', { ...filters.value, page }, { preserveState: true });
};

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
};

const getTypeBadgeClass = (type) => {
  const classes = {
    topup: 'badge-soft-success',
    platform_fee: 'badge-soft-warning',
    refund: 'badge-soft-info',
    admin_adjustment: 'badge-soft-primary',
    promotional_credit: 'badge-soft-secondary',
  };
  return classes[type] || 'badge-soft-secondary';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    completed: 'badge-soft-success',
    pending: 'badge-soft-warning',
    failed: 'badge-soft-danger',
  };
  return classes[status] || 'badge-soft-secondary';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
