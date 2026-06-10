<div class="d-flex justify-content-between flex-wrap gap-2">
    <template x-for="(stepKey, stepIndex) in stepFlow" :key="stepKey">
        <div class="wizard-step text-center flex-fill"
            :class="{ 'active': currentStep === stepIndex + 1, 'completed': currentStep > stepIndex + 1 }"
            @click="goToStep(stepIndex + 1)">
            <div class="step-number">
                <i class="bi bi-check" x-show="currentStep > stepIndex + 1"></i>
                <span x-show="currentStep <= stepIndex + 1" x-text="stepIndex + 1"></span>
            </div>
            <div class="step-title" x-text="stepLabel(stepKey)"></div>
        </div>
    </template>
</div>
