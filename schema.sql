--
-- PostgreSQL database dump
--

\restrict FgM5Ner7D1OKIGqY8KFQaXR6SxCttG5zu4DyTL5bhdznfNVKbhdQE1ercNuu2wC

-- Dumped from database version 15.16 (Debian 15.16-1.pgdg13+1)
-- Dumped by pg_dump version 15.16 (Debian 15.16-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activity_logs; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.activity_logs (
    id bigint NOT NULL,
    user_id bigint,
    action character varying(100) NOT NULL,
    model_type character varying(100),
    model_id bigint,
    old_values json,
    new_values json,
    ip_address character varying(45),
    user_agent character varying(255),
    description text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.activity_logs OWNER TO pos_user;

--
-- Name: activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.activity_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.activity_logs_id_seq OWNER TO pos_user;

--
-- Name: activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.activity_logs_id_seq OWNED BY public.activity_logs.id;


--
-- Name: bundle_package_items; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.bundle_package_items (
    id bigint NOT NULL,
    bundle_package_id bigint NOT NULL,
    product_id bigint NOT NULL,
    product_variant_id bigint,
    quantity integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bundle_package_items OWNER TO pos_user;

--
-- Name: bundle_package_items_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.bundle_package_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.bundle_package_items_id_seq OWNER TO pos_user;

--
-- Name: bundle_package_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.bundle_package_items_id_seq OWNED BY public.bundle_package_items.id;


--
-- Name: bundle_packages; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.bundle_packages (
    id bigint NOT NULL,
    store_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    image character varying(255),
    bundle_price numeric(12,2) NOT NULL,
    starts_at timestamp(0) without time zone,
    ends_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bundle_packages OWNER TO pos_user;

--
-- Name: bundle_packages_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.bundle_packages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.bundle_packages_id_seq OWNER TO pos_user;

--
-- Name: bundle_packages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.bundle_packages_id_seq OWNED BY public.bundle_packages.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO pos_user;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO pos_user;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    store_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    slug character varying(120) NOT NULL,
    description text,
    image character varying(255),
    sort_order integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    deleted_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categories OWNER TO pos_user;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.categories_id_seq OWNER TO pos_user;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.jobs OWNER TO pos_user;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.jobs_id_seq OWNER TO pos_user;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: kitchen_orders; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.kitchen_orders (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    status character varying(30) DEFAULT 'queued'::character varying NOT NULL,
    priority integer DEFAULT 0 NOT NULL,
    queued_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    cooking_started_at timestamp(0) without time zone,
    ready_at timestamp(0) without time zone,
    started_by bigint,
    completed_by bigint,
    kitchen_notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT kitchen_orders_status_check CHECK (((status)::text = ANY ((ARRAY['waiting_payment'::character varying, 'queued'::character varying, 'cooking'::character varying, 'ready'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.kitchen_orders OWNER TO pos_user;

--
-- Name: kitchen_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.kitchen_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kitchen_orders_id_seq OWNER TO pos_user;

--
-- Name: kitchen_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.kitchen_orders_id_seq OWNED BY public.kitchen_orders.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO pos_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO pos_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    type character varying(255) NOT NULL,
    notifiable_type character varying(255) NOT NULL,
    notifiable_id bigint NOT NULL,
    data text NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.notifications OWNER TO pos_user;

--
-- Name: order_items; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.order_items (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    product_id bigint NOT NULL,
    product_name character varying(100) NOT NULL,
    unit_price numeric(12,2) NOT NULL,
    quantity integer NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    special_notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    variant_id bigint,
    variant_name character varying(255),
    original_price numeric(10,2),
    discount_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    discount_label character varying(100),
    bundle_id bigint
);


ALTER TABLE public.order_items OWNER TO pos_user;

--
-- Name: order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.order_items_id_seq OWNER TO pos_user;

--
-- Name: order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.order_items_id_seq OWNED BY public.order_items.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.orders (
    id bigint NOT NULL,
    store_id bigint NOT NULL,
    cashier_id bigint NOT NULL,
    table_id bigint,
    order_number character varying(30) NOT NULL,
    order_type character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    subtotal numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    tax_rate numeric(5,2) DEFAULT '10'::numeric NOT NULL,
    tax_amount numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total_amount numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    cancel_reason text,
    cancelled_by bigint,
    cancelled_at timestamp(0) without time zone,
    cooking_at timestamp(0) without time zone,
    ready_at timestamp(0) without time zone,
    completed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sent_to_kitchen_at timestamp(0) without time zone,
    customer_name character varying(255),
    CONSTRAINT orders_order_type_check CHECK (((order_type)::text = ANY ((ARRAY['dine_in'::character varying, 'takeaway'::character varying])::text[]))),
    CONSTRAINT orders_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'cooking'::character varying, 'ready'::character varying, 'completed'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.orders OWNER TO pos_user;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.orders_id_seq OWNER TO pos_user;

--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO pos_user;

--
-- Name: payments; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.payments (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    cashier_id bigint NOT NULL,
    payment_method character varying(20) NOT NULL,
    ewallet_type character varying(30),
    card_type character varying(30),
    card_last_four character varying(4),
    approval_code character varying(50),
    reference_number character varying(100),
    amount numeric(12,2) NOT NULL,
    amount_received numeric(12,2),
    change_amount numeric(12,2),
    status character varying(20) DEFAULT 'paid'::character varying NOT NULL,
    refund_amount numeric(12,2),
    refund_reason text,
    refunded_at timestamp(0) without time zone,
    refunded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    gateway character varying(20),
    gateway_trx_id character varying(100),
    gateway_status character varying(50),
    snap_token character varying(255),
    payment_url character varying(500),
    qr_string text,
    gateway_response jsonb,
    settled_at timestamp(0) without time zone,
    va_number character varying(50),
    bank character varying(20),
    CONSTRAINT payments_payment_method_check CHECK (((payment_method)::text = ANY ((ARRAY['cash'::character varying, 'card'::character varying, 'ewallet'::character varying, 'qris'::character varying, 'bank_transfer'::character varying])::text[]))),
    CONSTRAINT payments_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'paid'::character varying, 'refunded'::character varying, 'partial'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.payments OWNER TO pos_user;

--
-- Name: COLUMN payments.gateway; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.gateway IS 'null=manual, midtrans=via Midtrans';


--
-- Name: COLUMN payments.gateway_trx_id; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.gateway_trx_id IS 'transaction_id dari Midtrans — kunci untuk webhook matching';


--
-- Name: COLUMN payments.gateway_status; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.gateway_status IS 'raw status dari gateway: settlement, capture, pending, deny, expire, cancel';


--
-- Name: COLUMN payments.snap_token; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.snap_token IS 'Snap token untuk pop-up Midtrans JS';


--
-- Name: COLUMN payments.payment_url; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.payment_url IS 'URL redirect alternatif jika tidak pakai Snap JS';


--
-- Name: COLUMN payments.qr_string; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.qr_string IS 'Base64 QR image atau raw QRIS string';


--
-- Name: COLUMN payments.gateway_response; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.gateway_response IS 'Raw JSON dari gateway untuk audit/debug';


--
-- Name: COLUMN payments.settled_at; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.settled_at IS 'Waktu webhook settlement/capture diterima';


--
-- Name: COLUMN payments.va_number; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.va_number IS 'Nomor Virtual Account dari Midtrans (BCA, BNI, BRI, Mandiri bill_key, Permata)';


--
-- Name: COLUMN payments.bank; Type: COMMENT; Schema: public; Owner: pos_user
--

COMMENT ON COLUMN public.payments.bank IS 'Nama bank untuk transfer: bca, bni, bri, mandiri, permata';


--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.payments_id_seq OWNER TO pos_user;

--
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- Name: product_discounts; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.product_discounts (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    type character varying(255) NOT NULL,
    value numeric(12,2) NOT NULL,
    min_order_amount numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    starts_at timestamp(0) without time zone,
    ends_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT product_discounts_type_check CHECK (((type)::text = ANY ((ARRAY['percentage'::character varying, 'fixed'::character varying])::text[])))
);


ALTER TABLE public.product_discounts OWNER TO pos_user;

--
-- Name: product_discounts_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.product_discounts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.product_discounts_id_seq OWNER TO pos_user;

--
-- Name: product_discounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.product_discounts_id_seq OWNED BY public.product_discounts.id;


--
-- Name: product_variants; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.product_variants (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    type character varying(50) NOT NULL,
    price_adjustment numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    stock integer DEFAULT 0 NOT NULL,
    is_available boolean DEFAULT true NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.product_variants OWNER TO pos_user;

--
-- Name: product_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.product_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.product_variants_id_seq OWNER TO pos_user;

--
-- Name: product_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.product_variants_id_seq OWNED BY public.product_variants.id;


--
-- Name: products; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.products (
    id bigint NOT NULL,
    category_id bigint,
    store_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    slug character varying(120) NOT NULL,
    description text,
    image character varying(255),
    price numeric(12,2) NOT NULL,
    stock integer DEFAULT 0 NOT NULL,
    low_stock_alert integer DEFAULT 10 NOT NULL,
    is_available boolean DEFAULT true NOT NULL,
    track_stock boolean DEFAULT true NOT NULL,
    deleted_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.products OWNER TO pos_user;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.products_id_seq OWNER TO pos_user;

--
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- Name: report_schedules; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.report_schedules (
    id bigint NOT NULL,
    created_by bigint NOT NULL,
    store_id bigint NOT NULL,
    report_type character varying(50) NOT NULL,
    frequency character varying(255) NOT NULL,
    send_at time(0) without time zone DEFAULT '08:00:00'::time without time zone NOT NULL,
    recipients json NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    last_sent_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT report_schedules_frequency_check CHECK (((frequency)::text = ANY ((ARRAY['daily'::character varying, 'weekly'::character varying, 'monthly'::character varying])::text[])))
);


ALTER TABLE public.report_schedules OWNER TO pos_user;

--
-- Name: report_schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.report_schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.report_schedules_id_seq OWNER TO pos_user;

--
-- Name: report_schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.report_schedules_id_seq OWNED BY public.report_schedules.id;


--
-- Name: reservations; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.reservations (
    id bigint NOT NULL,
    table_id bigint,
    created_by bigint NOT NULL,
    customer_name character varying(100) NOT NULL,
    customer_phone character varying(20),
    reserved_at timestamp(0) without time zone NOT NULL,
    expires_at timestamp(0) without time zone,
    guest_count integer,
    notes text,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    cancelled_at timestamp(0) without time zone,
    cancel_reason text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT reservations_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'converted'::character varying, 'cancelled'::character varying, 'expired'::character varying])::text[])))
);


ALTER TABLE public.reservations OWNER TO pos_user;

--
-- Name: reservations_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.reservations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.reservations_id_seq OWNER TO pos_user;

--
-- Name: reservations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.reservations_id_seq OWNED BY public.reservations.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(50) NOT NULL,
    slug character varying(50) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO pos_user;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO pos_user;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO pos_user;

--
-- Name: stock_logs; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.stock_logs (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    user_id bigint,
    order_id bigint,
    type character varying(255) NOT NULL,
    quantity_before integer NOT NULL,
    quantity_change integer NOT NULL,
    quantity_after integer NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT stock_logs_type_check CHECK (((type)::text = ANY ((ARRAY['in'::character varying, 'out'::character varying, 'adjustment'::character varying, 'cancel_restore'::character varying])::text[])))
);


ALTER TABLE public.stock_logs OWNER TO pos_user;

--
-- Name: stock_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.stock_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_logs_id_seq OWNER TO pos_user;

--
-- Name: stock_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.stock_logs_id_seq OWNED BY public.stock_logs.id;


--
-- Name: stores; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.stores (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    address text,
    phone character varying(20),
    email character varying(100),
    tax_number character varying(50),
    tax_rate numeric(5,2) DEFAULT '10'::numeric NOT NULL,
    receipt_footer text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    has_kitchen boolean DEFAULT true NOT NULL,
    logo_path character varying(255),
    is_headquarters boolean DEFAULT false NOT NULL
);


ALTER TABLE public.stores OWNER TO pos_user;

--
-- Name: stores_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.stores_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stores_id_seq OWNER TO pos_user;

--
-- Name: stores_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.stores_id_seq OWNED BY public.stores.id;


--
-- Name: tables; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.tables (
    id bigint NOT NULL,
    store_id bigint NOT NULL,
    number character varying(20) NOT NULL,
    capacity integer DEFAULT 4 NOT NULL,
    section character varying(50),
    status character varying(255) DEFAULT 'available'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT tables_status_check CHECK (((status)::text = ANY ((ARRAY['available'::character varying, 'occupied'::character varying, 'reserved'::character varying, 'closed'::character varying])::text[])))
);


ALTER TABLE public.tables OWNER TO pos_user;

--
-- Name: tables_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.tables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tables_id_seq OWNER TO pos_user;

--
-- Name: tables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.tables_id_seq OWNED BY public.tables.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: pos_user
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    role_id bigint NOT NULL,
    store_id bigint,
    name character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    password character varying(255) NOT NULL,
    phone character varying(20),
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    failed_login_attempts integer DEFAULT 0 NOT NULL,
    locked_until timestamp(0) without time zone,
    last_login_at timestamp(0) without time zone,
    remember_token character varying(100),
    deleted_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    email_verified_at timestamp(0) without time zone,
    CONSTRAINT users_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO pos_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: pos_user
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO pos_user;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pos_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: activity_logs id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.activity_logs ALTER COLUMN id SET DEFAULT nextval('public.activity_logs_id_seq'::regclass);


--
-- Name: bundle_package_items id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_package_items ALTER COLUMN id SET DEFAULT nextval('public.bundle_package_items_id_seq'::regclass);


--
-- Name: bundle_packages id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_packages ALTER COLUMN id SET DEFAULT nextval('public.bundle_packages_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: kitchen_orders id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.kitchen_orders ALTER COLUMN id SET DEFAULT nextval('public.kitchen_orders_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_items id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.order_items ALTER COLUMN id SET DEFAULT nextval('public.order_items_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- Name: product_discounts id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.product_discounts ALTER COLUMN id SET DEFAULT nextval('public.product_discounts_id_seq'::regclass);


--
-- Name: product_variants id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.product_variants ALTER COLUMN id SET DEFAULT nextval('public.product_variants_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: report_schedules id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.report_schedules ALTER COLUMN id SET DEFAULT nextval('public.report_schedules_id_seq'::regclass);


--
-- Name: reservations id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.reservations ALTER COLUMN id SET DEFAULT nextval('public.reservations_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: stock_logs id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stock_logs ALTER COLUMN id SET DEFAULT nextval('public.stock_logs_id_seq'::regclass);


--
-- Name: stores id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stores ALTER COLUMN id SET DEFAULT nextval('public.stores_id_seq'::regclass);


--
-- Name: tables id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.tables ALTER COLUMN id SET DEFAULT nextval('public.tables_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: activity_logs activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_pkey PRIMARY KEY (id);


--
-- Name: bundle_package_items bundle_package_items_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_package_items
    ADD CONSTRAINT bundle_package_items_pkey PRIMARY KEY (id);


--
-- Name: bundle_packages bundle_packages_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_packages
    ADD CONSTRAINT bundle_packages_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: kitchen_orders kitchen_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.kitchen_orders
    ADD CONSTRAINT kitchen_orders_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: order_items order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_pkey PRIMARY KEY (id);


--
-- Name: orders orders_order_number_unique; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_order_number_unique UNIQUE (order_number);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: product_discounts product_discounts_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.product_discounts
    ADD CONSTRAINT product_discounts_pkey PRIMARY KEY (id);


--
-- Name: product_variants product_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_pkey PRIMARY KEY (id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: report_schedules report_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_pkey PRIMARY KEY (id);


--
-- Name: reservations reservations_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_pkey PRIMARY KEY (id);


--
-- Name: roles roles_name_unique; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_unique UNIQUE (name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: roles roles_slug_unique; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_slug_unique UNIQUE (slug);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: stock_logs stock_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stock_logs
    ADD CONSTRAINT stock_logs_pkey PRIMARY KEY (id);


--
-- Name: stores stores_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stores
    ADD CONSTRAINT stores_pkey PRIMARY KEY (id);


--
-- Name: tables tables_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.tables
    ADD CONSTRAINT tables_pkey PRIMARY KEY (id);


--
-- Name: tables tables_store_id_number_unique; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.tables
    ADD CONSTRAINT tables_store_id_number_unique UNIQUE (store_id, number);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: activity_logs_model_type_model_id_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX activity_logs_model_type_model_id_index ON public.activity_logs USING btree (model_type, model_id);


--
-- Name: activity_logs_user_id_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX activity_logs_user_id_index ON public.activity_logs USING btree (user_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: categories_store_id_name_active_unique; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE UNIQUE INDEX categories_store_id_name_active_unique ON public.categories USING btree (store_id, name) WHERE (deleted_at IS NULL);


--
-- Name: notifications_notifiable_type_notifiable_id_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX notifications_notifiable_type_notifiable_id_index ON public.notifications USING btree (notifiable_type, notifiable_id);


--
-- Name: payments_gateway_status_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX payments_gateway_status_index ON public.payments USING btree (gateway, status);


--
-- Name: payments_gateway_trx_id_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX payments_gateway_trx_id_index ON public.payments USING btree (gateway_trx_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: users_email_unique; Type: INDEX; Schema: public; Owner: pos_user
--

CREATE UNIQUE INDEX users_email_unique ON public.users USING btree (email) WHERE (deleted_at IS NULL);


--
-- Name: activity_logs activity_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: bundle_package_items bundle_package_items_bundle_package_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_package_items
    ADD CONSTRAINT bundle_package_items_bundle_package_id_foreign FOREIGN KEY (bundle_package_id) REFERENCES public.bundle_packages(id) ON DELETE CASCADE;


--
-- Name: bundle_package_items bundle_package_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_package_items
    ADD CONSTRAINT bundle_package_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: bundle_package_items bundle_package_items_product_variant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_package_items
    ADD CONSTRAINT bundle_package_items_product_variant_id_foreign FOREIGN KEY (product_variant_id) REFERENCES public.product_variants(id) ON DELETE SET NULL;


--
-- Name: bundle_packages bundle_packages_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.bundle_packages
    ADD CONSTRAINT bundle_packages_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE CASCADE;


--
-- Name: categories categories_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE CASCADE;


--
-- Name: kitchen_orders kitchen_orders_completed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.kitchen_orders
    ADD CONSTRAINT kitchen_orders_completed_by_foreign FOREIGN KEY (completed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: kitchen_orders kitchen_orders_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.kitchen_orders
    ADD CONSTRAINT kitchen_orders_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: kitchen_orders kitchen_orders_started_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.kitchen_orders
    ADD CONSTRAINT kitchen_orders_started_by_foreign FOREIGN KEY (started_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: order_items order_items_bundle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_bundle_id_foreign FOREIGN KEY (bundle_id) REFERENCES public.bundle_packages(id) ON DELETE SET NULL;


--
-- Name: order_items order_items_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE RESTRICT;


--
-- Name: order_items order_items_variant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_variant_id_foreign FOREIGN KEY (variant_id) REFERENCES public.product_variants(id) ON DELETE SET NULL;


--
-- Name: orders orders_cancelled_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_cancelled_by_foreign FOREIGN KEY (cancelled_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: orders orders_cashier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_cashier_id_foreign FOREIGN KEY (cashier_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: orders orders_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE CASCADE;


--
-- Name: orders orders_table_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_table_id_foreign FOREIGN KEY (table_id) REFERENCES public.tables(id) ON DELETE SET NULL;


--
-- Name: payments payments_cashier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_cashier_id_foreign FOREIGN KEY (cashier_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: payments payments_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE RESTRICT;


--
-- Name: payments payments_refunded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_refunded_by_foreign FOREIGN KEY (refunded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: product_discounts product_discounts_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.product_discounts
    ADD CONSTRAINT product_discounts_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: product_variants product_variants_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: products products_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE RESTRICT;


--
-- Name: products products_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE CASCADE;


--
-- Name: report_schedules report_schedules_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: report_schedules report_schedules_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE CASCADE;


--
-- Name: reservations reservations_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: reservations reservations_table_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_table_id_foreign FOREIGN KEY (table_id) REFERENCES public.tables(id) ON DELETE SET NULL;


--
-- Name: stock_logs stock_logs_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stock_logs
    ADD CONSTRAINT stock_logs_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE SET NULL;


--
-- Name: stock_logs stock_logs_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stock_logs
    ADD CONSTRAINT stock_logs_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: stock_logs stock_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.stock_logs
    ADD CONSTRAINT stock_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: tables tables_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.tables
    ADD CONSTRAINT tables_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE CASCADE;


--
-- Name: users users_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE RESTRICT;


--
-- Name: users users_store_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: pos_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_store_id_foreign FOREIGN KEY (store_id) REFERENCES public.stores(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict FgM5Ner7D1OKIGqY8KFQaXR6SxCttG5zu4DyTL5bhdznfNVKbhdQE1ercNuu2wC

