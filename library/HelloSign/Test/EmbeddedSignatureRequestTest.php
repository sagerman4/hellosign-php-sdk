<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (C) 2014 hellosign.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace HelloSign\Test;

use HelloSign\Error;
use HelloSign\EmbeddedSignatureRequest;
use HelloSign\SignatureRequest;
use HelloSign\Template;
use HelloSign\TemplateSignatureRequest;
use HelloSign\UnclaimedDraft;

class EmbeddedSignatureRequestTest extends AbstractTest
{
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequest()
    {
        // Create the signature request
        $request = new SignatureRequest;
        $request->enableTestMode();
        $request->setTitle('Embedded NDA');
        $request->addSigner('jack@example.com', 'Jack');
        $request->addFile(__DIR__ . '/nda.docx');

        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);

        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);


        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());


        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }
    
    /**
     * @group create
     */
    public function testCreateEmbeddedRequesting()
    {
        // Create the signature request
        $request = new SignatureRequest;
        $request->enableTestMode();
        $request->setRequesterEmail('jolene_request1@example.com');
        $request->addFile(__DIR__ . '/nda.docx');

        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $draft_request = new UnclaimedDraft($request, $client_id);

        // Send it to HelloSign
        $response = $this->client->createUnclaimedDraft($draft_request);


        $this->assertInstanceOf('HelloSign\UnclaimedDraft', $response);
        $this->assertNotNull($response->getClaimUrl());
    }
    
/**
     * @group create
     */
    public function testCreateEmbeddedRequestingWithEmbeddedSigning()
    {
        // Create the signature request
        $request = new SignatureRequest;
        $request->enableTestMode();
        $request->setRequesterEmail('jolene_request2@example.com');
        $request->addFile(__DIR__ . '/nda.docx');

        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $draft_request = new UnclaimedDraft($request, $client_id);
        $draft_request->setIsForEmbeddedSigning(true);
        // Send it to HelloSign
        $response = $this->client->createUnclaimedDraft($draft_request);


        $this->assertInstanceOf('HelloSign\UnclaimedDraft', $response);
        $this->assertNotNull($response->getClaimUrl());
    }
    
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequestWithTemplate()
    {
        list($template, $request) = $this->getTemplateSignatureRequestObjects();

        foreach ($template->getSignerRoles() as $i => $role) {
            $request->setSigner($role->name, "george$i@example.com", "George {$role->name}");
        }
        foreach ($template->getCustomFields() as $i => $field) {
            $request->setCustomFieldValue($field->name, 'My String');
        }
        
        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);
        
        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }
    
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequestWithTemplateMissingSignerRole()
    {
        list($template, $request) = $this->getTemplateSignatureRequestObjects();

        if (count($template->getSignerRoles()) < 2) {
            throw new \IllegalArgumentException('Template must contain at least two signer roles for this test!');
        }

        $role = $template->getSignerRoles()[0];

        $request->setSigner($role->name, "george@example.com", "George {$role->name}");
        
        foreach ($template->getCustomFields() as $i => $field) {
            $request->setCustomFieldValue($field->name, 'My String');
        }
        
        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);
        
        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }
    
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequestWithTemplateInvalidCustomField()
    {
        $this->setExpectedException(Error::class, 'Invalid custom field: invalid_field');
        
        list($template, $request) = $this->getTemplateSignatureRequestObjects();

        foreach ($template->getSignerRoles() as $i => $role) {
            $request->setSigner($role->name, "george$i@example.com", "George {$role->name}");
        }
        foreach ($template->getCustomFields() as $i => $field) {
            $request->setCustomFieldValue($field->name, 'My String');
        }
        
        $request->setCustomFieldValue('invalid_field', 'My String');
        
        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);
        
        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }
    
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequestWithTemplateInvalidCustomFieldMissingSignerRoles()
    {
        $this->setExpectedException(Error::class, 'Response should be returned in JSON format');
        
        list($template, $request) = $this->getTemplateSignatureRequestObjects();

        if (count($template->getSignerRoles()) < 2) {
            throw new \IllegalArgumentException('Template must contain at least two signer roles for this test!');
        }

        $role = $template->getSignerRoles()[0];

        $request->setSigner($role->name, "george@example.com", "George {$role->name}");
        
        foreach ($template->getCustomFields() as $i => $field) {
            $request->setCustomFieldValue($field->name, 'My String');
        }
        
        // This needs to be unique, because this request errors out prematurely and 
        //   causes a template draft to be left open.  Repeats on the same request
        //   cause a different error.
        $invalid_field_name = uniqid('', true);
        
        $request->setCustomFieldValue($invalid_field_name, 'My String');
        
        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);
        
        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }
    
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequestWithTemplateInvalidCustomFieldMissingSignerRolesTryTwice()
    {
        $this->setExpectedException(Error::class, 'An identical request is already being processed.');
        
        list($template, $request) = $this->getTemplateSignatureRequestObjects();

        if (count($template->getSignerRoles()) < 2) {
            throw new \IllegalArgumentException('Template must contain at least two signer roles for this test!');
        }

        $role = $template->getSignerRoles()[0];

        $request->setSigner($role->name, "george@example.com", "George {$role->name}");
        
        foreach ($template->getCustomFields() as $i => $field) {
            $request->setCustomFieldValue($field->name, 'My String');
        }
        
        // This needs to be unique, because this request errors out prematurely and 
        //   causes a template draft to be left open.  Repeats on the same request
        //   cause a different error.  That's actually what we are testing in this test! ;)
        $invalid_field_name = uniqid('', true);
        
        $request->setCustomFieldValue($invalid_field_name, 'My String');
        
        // Turn it into an embedded request
        $client_id = $_ENV['CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);
        
        // Send it to HelloSign
        try {
            $this->client->createEmbeddedSignatureRequest($embedded_request);
        } catch (Error $e) {
            $this->assertSame('Response should be returned in JSON format', $e->getMessage());
        }
        
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }

    /**
     * @depends testCreateEmbeddedSignatureRequest
     * @group read
     */
    public function testGetEmbeddedSignUrl($id)
    {
        $response = $this->client->getEmbeddedSignUrl($id);
        $sign_url = $response->getSignUrl();

        $this->assertNotEmpty($sign_url);
    }
    
    private function getTemplateSignatureRequestObjects() {
        
        // Get a template
        
        $templates = $this->client->getTemplates();
        $template = $templates[0];
        
        // Create the signature request

        $request = new TemplateSignatureRequest;
        $request->enableTestMode();
        $request->setTemplateId($template->getId());
        $request->setSubject('Purchase Order');
        $request->setMessage('Glad we could come to an agreement.');
        
        foreach ($template->getCCRoles() as $i => $role) {
            $request->setCC($role->name, "oscar$i@example.com");
        }
        
        return [$template, $request];
    }
}
