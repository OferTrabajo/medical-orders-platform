# Medical Orders Platform

Sistema distribuido para la creación y validación de órdenes médicas, construido con una arquitectura desacoplada basada en microservicios, procesamiento asíncrono y contenedorización completa con Docker.

---

## Descripción general

Este proyecto permite:

- Crear órdenes médicas desde un frontend en Vue
- Procesarlas de forma asíncrona mediante colas (Redis)
- Validar reglas de negocio en un microservicio independiente (NestJS)
- Persistir información en MySQL
- Comunicar servicios de forma segura mediante JWT interno

---

## Arquitectura

[ Vue Frontend ]
↓
[ Laravel API ] ───────→ [ Redis Queue ]
↓ ↓
↓ [ Worker Laravel ]
↓ ↓
└────────→ [ NestJS Validator ]
↓
[ Response ]
↓
[ Update Order Status ]

---

## Tecnologías utilizadas

### Backend

- Laravel (PHP)
- NestJS (Node.js)
- MySQL
- Redis

### Frontend

- Vue 3 + Vite

### Infraestructura

- Docker
- Docker Compose

### Seguridad

- JWT entre servicios (Laravel → NestJS)

---

## Flujo de funcionamiento

1. El usuario crea una orden desde el frontend
2. Laravel recibe la solicitud y guarda la orden en estado `pending`
3. Laravel envía un Job a Redis
4. El Worker procesa el Job y envía la validación a NestJS
5. NestJS evalúa las reglas:
   - `service`: siempre válido
   - `medication`: inválido si precio > 20000
6. Nest responde con el resultado
7. Laravel actualiza la orden:
   - `approved`
   - `rejected`
   - `failed` (en caso de error)

---

## Seguridad interna

La comunicación entre Laravel y NestJS está protegida mediante JWT:

- Laravel genera un token con:
  - `iss`: laravel-app
  - `aud`: nest-validator
  - `scope`: internal-service
- NestJS valida:
  - firma
  - expiración
  - claims

Esto evita que el endpoint de validación quede expuesto públicamente.

---

## Ejecución con Docker

### Requisitos

- Docker
- Docker Compose

### Levantar todo el sistema

```bash
docker compose up --build
```

### Servicios disponibles

| Servicio         | URL                   |
| ---------------- | --------------------- |
| Frontend         | http://localhost:5173 |
| Laravel API      | http://localhost:8000 |
| NestJS Validator | http://localhost:3000 |
| MySQL            | localhost:3307        |
| Redis            | localhost:6379        |

---

## Pruebas

### Crear orden

POST /api/orders

#### Ejemplo válido

{
"patient_name": "Juan Perez",
"items": [
{ "type": "service", "name": "Consulta general", "price": 50000 },
{ "type": "medication", "name": "Ibuprofeno", "price": 15000 }
]
}

→ Resultado: approved

#### Ejemplo inválido

{
"patient_name": "Maria Gomez",
"items": [
{ "type": "medication", "name": "Medicamento caro", "price": 25000 }
]
}

→ Resultado: rejected

---

##  Decisiones técnicas

### 1. Separación de responsabilidades

- Laravel maneja orquestación y persistencia
- NestJS maneja reglas de negocio

### 2. Procesamiento asíncrono

Uso de colas con Redis para desacoplar la validación y mejorar escalabilidad.

### 3. Microservicio de validación

Permite escalar reglas de negocio de forma independiente.

### 4. JWT interno

Protege la comunicación entre servicios sin necesidad de autenticación compleja.

### 5. Dockerización completa

Todo el sistema se levanta con un solo comando, facilitando pruebas y despliegue.

---

##  Escalabilidad

Este diseño permite:

- Escalar horizontalmente el worker
- Escalar el servicio de validación independientemente
- agregar nuevos servicios sin afectar el core
- migrar a arquitectura de microservicios completa

---

##  Posibles mejoras

- Autenticación de usuarios (JWT externo)
- Uso de API Gateway
- Logs centralizados (ELK / Grafana)
- Retry y dead-letter queues
- Tests automatizados (unitarios e integración)
- WebSockets en lugar de polling
- CI/CD pipeline

---

## 👨‍💻 Autor

Proyecto desarrollado como prueba técnica.
