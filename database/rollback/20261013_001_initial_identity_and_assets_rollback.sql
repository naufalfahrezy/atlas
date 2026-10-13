-- ATLAS SQL ROLLBACK
-- File: 20261013_001_initial_identity_and_assets_rollback.sql
-- WARNING:
-- This removes the Iteration 1 ATLAS schema and its data.
-- Use only when rollback is explicitly intended in development.

BEGIN;

DROP TRIGGER IF EXISTS trg_auth_user_updated ON auth.users;
DROP TRIGGER IF EXISTS trg_auth_user_created ON auth.users;

DROP FUNCTION IF EXISTS public.sync_auth_user_profile();
DROP FUNCTION IF EXISTS public.handle_new_auth_user();

DROP TABLE IF EXISTS public.asset_status_history CASCADE;
DROP TABLE IF EXISTS public.assets CASCADE;
DROP TABLE IF EXISTS public.role_permissions CASCADE;
DROP TABLE IF EXISTS public.permissions CASCADE;
DROP TABLE IF EXISTS public.profiles CASCADE;
DROP TABLE IF EXISTS public.roles CASCADE;

DROP FUNCTION IF EXISTS public.current_user_has_permission(text);
DROP FUNCTION IF EXISTS public.current_user_has_role(text);
DROP FUNCTION IF EXISTS public.record_asset_change();
DROP FUNCTION IF EXISTS public.set_updated_at();

COMMIT;
