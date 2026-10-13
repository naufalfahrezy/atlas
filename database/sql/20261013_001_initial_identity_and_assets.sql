-- ATLAS SQL CHANGE
-- File: 20261013_001_initial_identity_and_assets.sql
-- Purpose: Initial schema for Iteration 1 (identity/authorization metadata and asset management)
-- Affected domains: Auth & User, Asset
-- Affected services: Auth and User Service, Asset Service
-- Preconditions:
--   1. Supabase project exists.
--   2. auth.users is available from Supabase Auth.
--   3. Run using Supabase SQL Editor or a PostgreSQL role allowed to create objects.
-- Rollback:
--   database/rollback/20261013_001_initial_identity_and_assets_rollback.sql
--
-- IMPORTANT:
--   * No Laravel/CodeIgniter migration is used.
--   * F02 and F03 are NOT stored files and are not part of Iteration 1.
--   * RLS is enabled explicitly even if "Enable automatic RLS" is active in Supabase.

BEGIN;

CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- ============================================================
-- Shared utility
-- ============================================================

CREATE OR REPLACE FUNCTION public.set_updated_at()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.updated_at := now();
    RETURN NEW;
END;
$$;

-- ============================================================
-- Identity and authorization metadata
-- ============================================================

CREATE TABLE IF NOT EXISTS public.roles (
    id            bigserial PRIMARY KEY,
    code          varchar(50)  NOT NULL UNIQUE,
    name          varchar(120) NOT NULL,
    description   text,
    is_active     boolean      NOT NULL DEFAULT true,
    created_at    timestamptz  NOT NULL DEFAULT now(),
    updated_at    timestamptz  NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS public.permissions (
    id            bigserial PRIMARY KEY,
    code          varchar(100) NOT NULL UNIQUE,
    name          varchar(150) NOT NULL,
    description   text,
    created_at    timestamptz  NOT NULL DEFAULT now(),
    updated_at    timestamptz  NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS public.role_permissions (
    role_id       bigint NOT NULL REFERENCES public.roles(id) ON DELETE CASCADE,
    permission_id bigint NOT NULL REFERENCES public.permissions(id) ON DELETE CASCADE,
    created_at    timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS public.profiles (
    id                     uuid PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    role_id                bigint NOT NULL REFERENCES public.roles(id),
    full_name              varchar(160),
    email                  varchar(255),
    institution_identifier varchar(100),
    phone                  varchar(50),
    is_active              boolean NOT NULL DEFAULT true,
    created_at             timestamptz NOT NULL DEFAULT now(),
    updated_at             timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_profiles_role_id
    ON public.profiles(role_id);

CREATE INDEX IF NOT EXISTS idx_profiles_email
    ON public.profiles(lower(email));

DROP TRIGGER IF EXISTS trg_roles_set_updated_at ON public.roles;
CREATE TRIGGER trg_roles_set_updated_at
BEFORE UPDATE ON public.roles
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_permissions_set_updated_at ON public.permissions;
CREATE TRIGGER trg_permissions_set_updated_at
BEFORE UPDATE ON public.permissions
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_profiles_set_updated_at ON public.profiles;
CREATE TRIGGER trg_profiles_set_updated_at
BEFORE UPDATE ON public.profiles
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- Canonical ATLAS roles.
INSERT INTO public.roles (code, name, description)
VALUES
    (
        'LOGISTICS_STAFF',
        'Staf Unit Logistik dan Laboratorium',
        'Role dengan kewenangan tertinggi pada ATLAS. Tidak ada role Administrator terpisah.'
    ),
    (
        'SSC',
        'Student Service Center (SSC)',
        'Melakukan verifikasi administratif pengajuan dan bukan approval tier.'
    ),
    (
        'STUDENT_AFFAIRS',
        'Bagian Kemahasiswaan',
        'Memberikan keputusan persetujuan setelah keputusan Unit Logistik dan Laboratorium.'
    ),
    (
        'PRACTICUM_ASSISTANT',
        'Asisten Praktikum',
        'Mengakses jadwal dan fungsi presensi sesuai kewenangan.'
    ),
    (
        'BORROWER',
        'Pemohon Peminjaman Fasilitas',
        'Mengajukan peminjaman fasilitas dan memantau pengajuan miliknya.'
    )
ON CONFLICT (code) DO UPDATE
SET
    name = EXCLUDED.name,
    description = EXCLUDED.description,
    is_active = true,
    updated_at = now();

-- Iteration 1 permissions.
INSERT INTO public.permissions (code, name, description)
VALUES
    ('users.view', 'Lihat Pengguna', 'Melihat data akun pengguna sesuai kewenangan.'),
    ('users.manage', 'Kelola Pengguna', 'Menambah dan memperbarui data akun pengguna.'),
    ('roles.manage', 'Kelola Peran dan Hak Akses', 'Menetapkan role dan permission pengguna.'),
    ('assets.view', 'Lihat Aset', 'Melihat daftar dan detail aset.'),
    ('assets.create', 'Tambah Aset', 'Menambahkan data aset.'),
    ('assets.update', 'Ubah Aset', 'Memperbarui data aset.'),
    ('assets.status.update', 'Ubah Status Aset', 'Memperbarui kondisi atau status ketersediaan aset.'),
    ('assets.history.view', 'Lihat Riwayat Aset', 'Melihat riwayat perubahan aset.')
ON CONFLICT (code) DO UPDATE
SET
    name = EXCLUDED.name,
    description = EXCLUDED.description,
    updated_at = now();

-- Logistics Staff owns all Iteration 1 permissions.
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM public.roles r
CROSS JOIN public.permissions p
WHERE r.code = 'LOGISTICS_STAFF'
ON CONFLICT DO NOTHING;

-- New Supabase Auth users receive BORROWER as the safe default application role.
-- A Logistics Staff user can later assign a different role via Auth & User Service.
CREATE OR REPLACE FUNCTION public.handle_new_auth_user()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
    default_role_id bigint;
BEGIN
    SELECT id
      INTO default_role_id
      FROM public.roles
     WHERE code = 'BORROWER'
       AND is_active = true
     LIMIT 1;

    IF default_role_id IS NULL THEN
        RAISE EXCEPTION 'ATLAS default BORROWER role is not available';
    END IF;

    INSERT INTO public.profiles (
        id,
        role_id,
        full_name,
        email
    )
    VALUES (
        NEW.id,
        default_role_id,
        NULLIF(COALESCE(NEW.raw_user_meta_data ->> 'full_name', ''), ''),
        NEW.email
    )
    ON CONFLICT (id) DO NOTHING;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_auth_user_created ON auth.users;
CREATE TRIGGER trg_auth_user_created
AFTER INSERT ON auth.users
FOR EACH ROW EXECUTE FUNCTION public.handle_new_auth_user();

CREATE OR REPLACE FUNCTION public.sync_auth_user_profile()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    UPDATE public.profiles
       SET email = NEW.email,
           full_name = COALESCE(
               NULLIF(NEW.raw_user_meta_data ->> 'full_name', ''),
               full_name
           ),
           updated_at = now()
     WHERE id = NEW.id;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_auth_user_updated ON auth.users;
CREATE TRIGGER trg_auth_user_updated
AFTER UPDATE OF email, raw_user_meta_data ON auth.users
FOR EACH ROW EXECUTE FUNCTION public.sync_auth_user_profile();

-- ============================================================
-- Asset domain
-- ============================================================

CREATE TABLE IF NOT EXISTS public.asset_batches (
    id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    input_method  varchar(30) NOT NULL DEFAULT 'SINGLE'
                  CHECK (input_method IN ('SINGLE', 'BULK_FORM')),
    notes         text,
    created_by    uuid REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at    timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_asset_batches_created_at
    ON public.asset_batches(created_at DESC);

CREATE INDEX IF NOT EXISTS idx_asset_batches_created_by
    ON public.asset_batches(created_by);

CREATE TABLE IF NOT EXISTS public.assets (
    id                  uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    batch_id            uuid NOT NULL
                        REFERENCES public.asset_batches(id) ON DELETE RESTRICT,
    name                varchar(200) NOT NULL,
    category            varchar(120) NOT NULL,
    location            varchar(200),
    condition           varchar(40) NOT NULL DEFAULT 'BAIK'
                        CHECK (
                            condition IN (
                                'BAIK',
                                'RUSAK_RINGAN',
                                'RUSAK',
                                'PERLU_PEMERIKSAAN'
                            )
                        ),
    availability_status varchar(40) NOT NULL DEFAULT 'TERSEDIA'
                        CHECK (
                            availability_status IN (
                                'TERSEDIA',
                                'DIPINJAM',
                                'TIDAK_TERSEDIA',
                                'PERLU_PEMERIKSAAN'
                            )
                        ),
    notes               text,
    created_by          uuid REFERENCES public.profiles(id) ON DELETE SET NULL,
    updated_by          uuid REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at          timestamptz NOT NULL DEFAULT now(),
    updated_at          timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_assets_batch_id
    ON public.assets(batch_id);

CREATE INDEX IF NOT EXISTS idx_assets_name
    ON public.assets(name);

CREATE INDEX IF NOT EXISTS idx_assets_category
    ON public.assets(category);

CREATE INDEX IF NOT EXISTS idx_assets_location
    ON public.assets(location);

CREATE INDEX IF NOT EXISTS idx_assets_availability_status
    ON public.assets(availability_status);

CREATE TABLE IF NOT EXISTS public.asset_status_history (
    id                    uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    asset_id              uuid NOT NULL REFERENCES public.assets(id) ON DELETE CASCADE,
    previous_condition    varchar(40),
    new_condition         varchar(40),
    previous_status       varchar(40),
    new_status            varchar(40),
    previous_location     varchar(200),
    new_location          varchar(200),
    notes                 text,
    changed_by            uuid REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at            timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_asset_status_history_asset_created
    ON public.asset_status_history(asset_id, created_at DESC);

DROP TRIGGER IF EXISTS trg_assets_set_updated_at ON public.assets;
CREATE TRIGGER trg_assets_set_updated_at
BEFORE UPDATE ON public.assets
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- Batch membership represents the original input transaction and must not change.
CREATE OR REPLACE FUNCTION public.prevent_asset_batch_change()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.batch_id IS DISTINCT FROM OLD.batch_id THEN
        RAISE EXCEPTION 'asset batch membership is immutable';
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_assets_prevent_batch_change ON public.assets;
CREATE TRIGGER trg_assets_prevent_batch_change
BEFORE UPDATE ON public.assets
FOR EACH ROW EXECUTE FUNCTION public.prevent_asset_batch_change();

CREATE OR REPLACE FUNCTION public.record_asset_change()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        INSERT INTO public.asset_status_history (
            asset_id,
            new_condition,
            new_status,
            new_location,
            notes,
            changed_by
        )
        VALUES (
            NEW.id,
            NEW.condition,
            NEW.availability_status,
            NEW.location,
            'Initial asset record',
            NEW.created_by
        );

        RETURN NEW;
    END IF;

    IF
        OLD.condition IS DISTINCT FROM NEW.condition
        OR OLD.availability_status IS DISTINCT FROM NEW.availability_status
        OR OLD.location IS DISTINCT FROM NEW.location
    THEN
        INSERT INTO public.asset_status_history (
            asset_id,
            previous_condition,
            new_condition,
            previous_status,
            new_status,
            previous_location,
            new_location,
            notes,
            changed_by
        )
        VALUES (
            NEW.id,
            OLD.condition,
            NEW.condition,
            OLD.availability_status,
            NEW.availability_status,
            OLD.location,
            NEW.location,
            'Asset operational state changed',
            NEW.updated_by
        );
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_assets_record_change ON public.assets;
CREATE TRIGGER trg_assets_record_change
AFTER INSERT OR UPDATE ON public.assets
FOR EACH ROW EXECUTE FUNCTION public.record_asset_change();

-- ============================================================
-- Authorization helper functions
-- Used as defense-in-depth for Supabase Data API / RLS.
-- Main ATLAS business traffic still goes through backend services.
-- ============================================================

CREATE OR REPLACE FUNCTION public.current_user_has_role(required_role_code text)
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1
          FROM public.profiles p
          JOIN public.roles r ON r.id = p.role_id
         WHERE p.id = auth.uid()
           AND p.is_active = true
           AND r.is_active = true
           AND r.code = required_role_code
    );
$$;

CREATE OR REPLACE FUNCTION public.current_user_has_permission(required_permission_code text)
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1
          FROM public.profiles p
          JOIN public.roles r ON r.id = p.role_id
          JOIN public.role_permissions rp ON rp.role_id = r.id
          JOIN public.permissions perm ON perm.id = rp.permission_id
         WHERE p.id = auth.uid()
           AND p.is_active = true
           AND r.is_active = true
           AND perm.code = required_permission_code
    );
$$;

REVOKE ALL ON FUNCTION public.current_user_has_role(text) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.current_user_has_permission(text) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.current_user_has_role(text) TO authenticated;
GRANT EXECUTE ON FUNCTION public.current_user_has_permission(text) TO authenticated;

-- ============================================================
-- RLS
-- ============================================================

ALTER TABLE public.roles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.permissions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.role_permissions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.asset_batches ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.assets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.asset_status_history ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS roles_read_authenticated ON public.roles;
CREATE POLICY roles_read_authenticated
ON public.roles
FOR SELECT
TO authenticated
USING (true);

DROP POLICY IF EXISTS permissions_read_authenticated ON public.permissions;
CREATE POLICY permissions_read_authenticated
ON public.permissions
FOR SELECT
TO authenticated
USING (true);

DROP POLICY IF EXISTS profiles_read_self ON public.profiles;
CREATE POLICY profiles_read_self
ON public.profiles
FOR SELECT
TO authenticated
USING (id = auth.uid());

DROP POLICY IF EXISTS profiles_logistics_read_all ON public.profiles;
CREATE POLICY profiles_logistics_read_all
ON public.profiles
FOR SELECT
TO authenticated
USING (public.current_user_has_role('LOGISTICS_STAFF'));

DROP POLICY IF EXISTS profiles_logistics_update ON public.profiles;
CREATE POLICY profiles_logistics_update
ON public.profiles
FOR UPDATE
TO authenticated
USING (public.current_user_has_permission('users.manage'))
WITH CHECK (public.current_user_has_permission('users.manage'));

DROP POLICY IF EXISTS asset_batches_read_authenticated ON public.asset_batches;
CREATE POLICY asset_batches_read_authenticated
ON public.asset_batches
FOR SELECT
TO authenticated
USING (true);

DROP POLICY IF EXISTS asset_batches_logistics_insert ON public.asset_batches;
CREATE POLICY asset_batches_logistics_insert
ON public.asset_batches
FOR INSERT
TO authenticated
WITH CHECK (public.current_user_has_permission('assets.create'));

DROP POLICY IF EXISTS asset_batches_logistics_update ON public.asset_batches;
CREATE POLICY asset_batches_logistics_update
ON public.asset_batches
FOR UPDATE
TO authenticated
USING (public.current_user_has_permission('assets.update'))
WITH CHECK (public.current_user_has_permission('assets.update'));

DROP POLICY IF EXISTS assets_read_authenticated ON public.assets;
CREATE POLICY assets_read_authenticated
ON public.assets
FOR SELECT
TO authenticated
USING (true);

DROP POLICY IF EXISTS assets_logistics_insert ON public.assets;
CREATE POLICY assets_logistics_insert
ON public.assets
FOR INSERT
TO authenticated
WITH CHECK (public.current_user_has_permission('assets.create'));

DROP POLICY IF EXISTS assets_logistics_update ON public.assets;
CREATE POLICY assets_logistics_update
ON public.assets
FOR UPDATE
TO authenticated
USING (public.current_user_has_permission('assets.update'))
WITH CHECK (public.current_user_has_permission('assets.update'));

DROP POLICY IF EXISTS asset_history_logistics_read ON public.asset_status_history;
CREATE POLICY asset_history_logistics_read
ON public.asset_status_history
FOR SELECT
TO authenticated
USING (public.current_user_has_permission('assets.history.view'));

-- Intentionally no policies for anon.
-- "Automatically expose new tables" should remain disabled.
-- Explicit Data API grants can be added later only where required.

COMMIT;

-- ============================================================
-- Optional verification queries
-- Run after the script if required.
-- ============================================================

-- SELECT code, name FROM public.roles ORDER BY id;
-- SELECT code, name FROM public.permissions ORDER BY id;
-- SELECT r.code AS role_code, p.code AS permission_code
-- FROM public.role_permissions rp
-- JOIN public.roles r ON r.id = rp.role_id
-- JOIN public.permissions p ON p.id = rp.permission_id
-- ORDER BY r.code, p.code;
--
-- SELECT tablename, rowsecurity
-- FROM pg_tables
-- WHERE schemaname = 'public'
--   AND tablename IN (
--       'roles',
--       'permissions',
--       'role_permissions',
--       'profiles',
--       'asset_batches',
--       'assets',
--       'asset_status_history'
--   )
-- ORDER BY tablename;


-- Asset batch verification example:
-- SELECT b.id AS batch_id, COUNT(a.id) AS asset_count
-- FROM public.asset_batches b
-- LEFT JOIN public.assets a ON a.batch_id = b.id
-- GROUP BY b.id
-- ORDER BY b.created_at DESC;
