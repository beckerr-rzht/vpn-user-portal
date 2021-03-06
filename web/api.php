<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

require_once dirname(__DIR__).'/vendor/autoload.php';
$baseDir = dirname(__DIR__);

use fkooman\Jwt\Keys\EdDSA\SecretKey;
use LC\Portal\CA\EasyRsaCa;
use LC\Portal\Config\PortalConfig;
use LC\Portal\FileIO;
use LC\Portal\Http\BearerAuthenticationHook;
use LC\Portal\Http\JsonResponse;
use LC\Portal\Http\Request;
use LC\Portal\Http\Service;
use LC\Portal\Http\VpnApiModule;
use LC\Portal\Init;
use LC\Portal\OAuth\BearerValidator;
use LC\Portal\OAuth\ClientDb;
use LC\Portal\OpenVpn\TlsCrypt;
use LC\Portal\Storage;
use Psr\Log\NullLogger;

$logger = new NullLogger();

try {
    $request = new Request($_SERVER, $_GET, $_POST);

    $dataDir = sprintf('%s/data', $baseDir);
    $configDir = sprintf('%s/config', $baseDir);

    $init = new Init($baseDir);
    $init->init();

    $portalConfig = PortalConfig::fromFile(sprintf('%s/config.php', $configDir));
    $service = new Service();

    if (false !== $portalConfig->getEnableApi()) {
        $apiConfig = $portalConfig->getApiConfig();
        $storage = new Storage(
            new PDO(sprintf('sqlite://%s/db.sqlite', $dataDir)),
            sprintf('%s/schema', $baseDir)
        );

        $keyInstanceMapping = [];
        if (true === $remoteAccess = $apiConfig->getRemoteAccess()) {
            $keyInstanceMappingFile = sprintf('%s/key_instance_mapping.json', $dataDir);
            if (FileIO::exists($keyInstanceMappingFile)) {
                $keyInstanceMapping = FileIO::readJsonFile($keyInstanceMappingFile);
            }
        }

        $secretKey = SecretKey::fromEncodedString(
            FileIO::readFile(
                sprintf('%s/oauth.key', $dataDir)
            )
        );

        $bearerValidator = new BearerValidator(
            $storage,
            new ClientDb(),
            $secretKey->getPublicKey(),
            $keyInstanceMapping
        );

        $service->addBeforeHook(
            'auth',
            new BearerAuthenticationHook(
                $bearerValidator
            )
        );

        $easyRsaDir = sprintf('%s/easy-rsa', $baseDir);
        $easyRsaDataDir = sprintf('%s/easy-rsa', $dataDir);
        $easyRsaCa = new EasyRsaCa(
            $easyRsaDir,
            $easyRsaDataDir
        );
        $tlsCrypt = TlsCrypt::fromFile(sprintf('%s/tls-crypt.key', $dataDir));

        // api module
        $vpnApiModule = new VpnApiModule(
            $storage,
            $portalConfig,
            $easyRsaCa,
            $tlsCrypt
        );
        $service->addModule($vpnApiModule);
    }
    $service->run($request)->send();
} catch (Exception $e) {
    $logger->error($e->getMessage());
    $response = new JsonResponse(['error' => $e->getMessage()], 500);
    $response->send();
}
