<template>
  <div>
    <div class="page-content">
      <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
          <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
              <h4 class="mb-sm-0">Notifications</h4>
              <div class="page-title-right">
                <button class="btn btn-soft-primary btn-sm" @click="markAllAsRead">
                  <i class="ri-check-double-line align-middle"></i> Mark All as Read
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
          <div class="col-md-3">
            <select class="form-select" v-model="filters.type" @change="applyFilters">
              <option value="">All Types</option>
              <option value="booking">Booking</option>
              <option value="trip">Trip</option>
              <option value="wallet">Wallet</option>
              <option value="safety">Safety</option>
              <option value="system">System</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="filters.read_status" @change="applyFilters">
              <option value="">All Status</option>
              <option value="unread">Unread</option>
              <option value="read">Read</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" class="form-control" placeholder="Search by user..." v-model="filters.user_id" @keyup.enter="applyFilters">
          </div>
          <div class="col-md-3">
            <button class="btn btn-primary" @click="applyFilters">Apply Filters</button>
            <button class="btn btn-soft-secondary ms-2" @click="resetFilters">Reset</button>
          </div>
        </div>

        <!-- Notifications Table -->
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
                        <th>Title</th>
                        <th>Body</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="notification in notifications.data" :key="notification.id" :class="{ 'table-active': !notification.read_at }">
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                              <span class="text-primary fw-bold">{{ getInitials(notification.user?.name) }}</span>
                            </div>
                            <div class="ms-2">
                              <h6 class="mb-0">{{ notification.user?.name || 'System' }}</h6>
                              <small class="text-muted">{{ notification.user?.email }}</small>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="badge" :class="getTypeBadgeClass(notification.type)">
                            {{ notification.type }}
                          </span>
                        </td>
                        <td>{{ notification.title }}</td>
                        <td class="text-muted" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                          {{ notification.body }}
                        </td>
                        <td>
                          <span v-if="notification.read_at" class="badge badge-soft-success">Read</span>
                          <span v-else class="badge badge-soft-warning">Unread</span>
                        </td>
                        <td>{{ formatDate(notification.created_at) }}</td>
                        <td>
                          <button v-if="!notification.read_at" class="btn btn-sm btn-soft-primary" @click="markAsRead(notification.id)">
                            <i class="ri-check-line"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="notifications.data?.length === 0">
                        <td colspan="7" class="text-center py-4 text-muted">No notifications found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3" v-if="notifications.last_page > 1">
                  <div class="text-muted">
                    Showing {{ notifications.from }} to {{ notifications.to }} of {{ notifications.total }} entries
                  </div>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !notifications.prev_page_url }">
                      <a class="page-link" @click="goToPage(notifications.current_page - 1)">Previous</a>
                    </li>
                    <li class="page-item" v-for="page in visiblePages" :key="page" :class="{ active: page === notifications.current_page }">
                      <a class="page-link" @click="goToPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: !notifications.next_page_url }">
                      <a class="page-link" @click="goToPage(notifications.current_page + 1)">Next</a>
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
  notifications: Object,
  filters: Object,
});

const filters = ref({
  type: props.filters?.type || '',
  read_status: props.filters?.read_status || '',
  user_id: props.filters?.user_id || '',
});

const visiblePages = computed(() => {
  const pages = [];
  const current = props.notifications.current_page;
  const last = props.notifications.last_page;
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const applyFilters = () => {
  router.get('/admin/notifications', filters.value, { preserveState: true });
};

const resetFilters = () => {
  filters.value = { type: '', read_status: '', user_id: '' };
  applyFilters();
};

const goToPage = (page) => {
  router.get('/admin/notifications', { ...filters.value, page }, { preserveState: true });
};

const markAsRead = (id) => {
  router.patch(`/admin/notifications/${id}/read`);
};

const markAllAsRead = () => {
  router.patch('/admin/notifications/read-all');
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getTypeBadgeClass = (type) => {
  const classes = {
    booking: 'badge-soft-primary',
    trip: 'badge-soft-info',
    wallet: 'badge-soft-success',
    safety: 'badge-soft-danger',
    system: 'badge-soft-secondary',
  };
  return classes[type] || 'badge-soft-secondary';
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
