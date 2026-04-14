<template>
  <div class="container">
    <h1>Medical Orders</h1>

    <div class="card">
      <label class="label">Patient name</label>
      <input v-model="patientName" class="input" placeholder="Juan Perez" />

      <h2>Items</h2>

      <div v-for="(item, index) in items" :key="index" class="item-row">
        <select v-model="item.type" class="input">
          <option value="service">service</option>
          <option value="medication">medication</option>
        </select>

        <input v-model="item.name" class="input" placeholder="Item name" />

        <input
          v-model.number="item.price"
          class="input"
          type="number"
          min="0"
          placeholder="Price"
        />

        <button
          class="danger-btn"
          @click="removeItem(index)"
          :disabled="items.length === 1"
        >
          Remove
        </button>
      </div>

      <button class="secondary-btn" @click="addItem">Add item</button>

      <div class="actions">
        <button class="primary-btn" @click="createOrder" :disabled="loading">
          {{ loading ? "Creating..." : "Create order" }}
        </button>
      </div>
    </div>

    <div v-if="errorMessage" class="card error">
      <strong>Error:</strong> {{ errorMessage }}
    </div>

    <div v-if="orderId" class="card">
      <h2>Order result</h2>
      <p><strong>Order ID:</strong> {{ orderId }}</p>
      <p><strong>Status:</strong> {{ status }}</p>
      <p v-if="reason"><strong>Reason:</strong> {{ reason }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

type ItemType = "service" | "medication";

interface OrderItemInput {
  type: ItemType;
  name: string;
  price: number;
}

interface CreateOrderResponse {
  order_id: number;
  status: string;
}

interface OrderResponse {
  id: number;
  patient_name: string;
  status: string;
  validation_reason: string | null;
  items: Array<{
    id: number;
    order_id: number;
    type: string;
    name: string;
    price: string;
    created_at: string;
    updated_at: string;
  }>;
}

const API_BASE_URL = "http://127.0.0.1:8000/api";

const patientName = ref("");
const items = ref<OrderItemInput[]>([{ type: "service", name: "", price: 0 }]);

const orderId = ref<number | null>(null);
const status = ref<string | null>(null);
const reason = ref<string | null>(null);
const loading = ref(false);
const errorMessage = ref<string | null>(null);

function addItem() {
  items.value.push({
    type: "service",
    name: "",
    price: 0,
  });
}

function removeItem(index: number) {
  if (items.value.length === 1) return;
  items.value.splice(index, 1);
}

async function createOrder() {
  errorMessage.value = null;
  reason.value = null;
  orderId.value = null;
  status.value = null;

  if (!patientName.value.trim()) {
    errorMessage.value = "Patient name is required.";
    return;
  }

  const hasInvalidItem = items.value.some(
    (item) => !item.name.trim() || item.price < 0,
  );

  if (hasInvalidItem) {
    errorMessage.value = "All items must have a name and a valid price.";
    return;
  }

  loading.value = true;

  try {
    const response = await fetch(`${API_BASE_URL}/orders`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        patient_name: patientName.value,
        items: items.value,
      }),
    });

    if (!response.ok) {
      const errorBody = await response.text();
      throw new Error(`Failed to create order. ${errorBody}`);
    }

    const data: CreateOrderResponse = await response.json();

    orderId.value = data.order_id;
    status.value = data.status;

    await pollOrderStatus(data.order_id);
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : "Unexpected error";
  } finally {
    loading.value = false;
  }
}

async function pollOrderStatus(id: number) {
  const maxAttempts = 15;

  for (let attempt = 0; attempt < maxAttempts; attempt += 1) {
    await new Promise((resolve) => setTimeout(resolve, 1500));

    const response = await fetch(`${API_BASE_URL}/orders/${id}`);

    if (!response.ok) {
      throw new Error("Failed to fetch order status.");
    }

    const data: OrderResponse = await response.json();

    status.value = data.status;
    reason.value = data.validation_reason;

    if (data.status !== "pending") {
      return;
    }
  }

  errorMessage.value = "Order is still pending after several attempts.";
}
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 32px 16px;
  font-family: Arial, Helvetica, sans-serif;
}

.card {
  border: 1px solid #dcdcdc;
  border-radius: 12px;
  padding: 20px;
  margin-top: 20px;
}

.label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
}

.input {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 12px;
  border: 1px solid #cfcfcf;
  border-radius: 8px;
  box-sizing: border-box;
}

.item-row {
  border: 1px solid #ececec;
  border-radius: 10px;
  padding: 12px;
  margin-bottom: 12px;
}

.actions {
  margin-top: 16px;
}

.primary-btn,
.secondary-btn,
.danger-btn {
  border: none;
  border-radius: 8px;
  padding: 10px 14px;
  cursor: pointer;
}

.primary-btn {
  font-weight: 700;
}

.secondary-btn,
.danger-btn {
  margin-top: 8px;
  margin-right: 8px;
}

.error {
  border-color: #d9534f;
}
</style>
